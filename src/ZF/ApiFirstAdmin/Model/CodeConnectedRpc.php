<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\Filter\FilterChain;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\ApiFirstAdmin\Exception;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ModuleUtils;

class CodeConnectedRpc
{
    /**
     * @var ConfigResource
     */
    protected $configResource;

    /**
     * @var FilterChain
     */
    protected $filter;
        
    /**
     * @var string
     */
    protected $module;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @param  string $module 
     * @param  ModuleUtils $modules 
     * @param  ConfigResource $config 
     */
    public function __construct($module, ModuleUtils $modules, ConfigResource $config)
    {
        $this->module         = $module;
        $this->modules        = $modules;
        $this->configResource = $config;
    }

    /**
     * Fetch a single RPC endpoint
     * 
     * @todo   get route details?
     * @param  string $controllerServiceName 
     * @return RpcEndpointMetadata|false
     */
    public function fetch($controllerServiceName)
    {
        $data   = array('controller_service_name' => $controllerServiceName);
        $config = $this->configResource->fetch(true);
        if (isset($config['zf-rpc'])
            && isset($config['zf-rpc'][$controllerServiceName])
        ) {
            $rpcConfig = $config['zf-rpc'][$controllerServiceName];
            if (isset($rpcConfig['route_name'])) {
                $data['route_name']  = $rpcConfig['route_name'];
                $data['route_match'] = $this->getRouteMatchStringFromModuleConfig($data['route_name'], $config);
            }
            if (isset($rpcConfig['http_methods'])) {
                $data['http_methods'] = $rpcConfig['http_methods'];
            }
        } else {
            return false;
        }

        if (isset($config['zf-content-negotiation'])) {
            $contentNegotiationConfig = $config['zf-content-negotiation'];
            if (isset($contentNegotiationConfig['controllers'])
                && isset($contentNegotiationConfig['controllers'][$controllerServiceName])
            ) {
                $data['selector'] = $contentNegotiationConfig['controllers'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['accept-whitelist'])
                && isset($contentNegotiationConfig['accept-whitelist'][$controllerServiceName])
            ) {
                $data['accept_whitelist'] = $contentNegotiationConfig['accept-whitelist'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['content-type-whitelist'])
                && isset($contentNegotiationConfig['content-type-whitelist'][$controllerServiceName])
            ) {
                $data['content_type_whitelist'] = $contentNegotiationConfig['content-type-whitelist'][$controllerServiceName];
            }
        }

        $endpoint = new RpcEndpointMetadata();
        $endpoint->exchangeArray($data);
        return $endpoint;
    }

    /**
     * Fetch all endpoints
     * 
     * @return RpcEndpointMetadata[]
     */
    public function fetchAll()
    {
        $config = $this->configResource->fetch(true);
        if (!isset($config['zf-rpc'])) {
            return array();
        }

        $endpoints = array();
        foreach (array_keys($config['zf-rpc']) as $service) {
            $endpoints[] = $this->fetch($service);
        }

        return $endpoints;
    }

    /**
     * Create a new RPC service in this module
     *
     * Creates the controller and all configuration, returning the full configuration as a tree.
     *
     * @todo   Return the controller service name
     * @param  string $serviceName 
     * @param  string $route 
     * @param  array $httpMethods 
     * @param  null|string $selector
     * @return RpcEndpointMetadata
     */
    public function createService($serviceName, $route, $httpMethods, $selector = null)
    {
        $controllerData = $this->createController($serviceName);
        $controllerService = $controllerData->service;
        $routeName      = $this->createRoute($route, $serviceName, $controllerService);
        $this->createRpcConfig($controllerService, $routeName, $httpMethods);
        $this->createSelectorConfig($controllerService, $selector);
        return $this->fetch($controllerService);
    }

    public function patchService($controllerServiceName, RpcEndpointMetadata $updates)
    {
        $original = $this->fetch($controllerServiceName);
        $merged   = array_merge(
            $original->getArrayCopy(),
            $updates->getArrayCopy()
        );

    }

    /**
     * Create a controller in the current module named for the given service
     * 
     * @param  string $serviceName 
     * @return stdClass
     */
    public function createController($serviceName)
    {
        $module     = $this->module;
        $modulePath = $this->modules->getModulePath($module);

        $srcPath = sprintf(
            '%s/src/%s/Controller',
            $modulePath,
            str_replace('\\', '/', $module)
        );

        if (!file_exists($srcPath)) {
            mkdir($srcPath, 0777, true);
        }

        $className         = sprintf('%sController', ucfirst($serviceName));
        $classPath         = sprintf('%s/%s.php', $srcPath, $className);
        $controllerService = sprintf('%s\Controller\\%s', $module, ucfirst($serviceName));

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller "%s" already exists',
                $className
            ));
        }
        
        $view = new ViewModel(array(
            'module'      => $module,
            'classname'   => $className,
            'servicename' => lcfirst($serviceName)
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
            'code-connected/rpc-controller' => __DIR__ . '/../../../../view/code-connected/rpc-controller.phtml'
        ));

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents($classPath,
            "<" . "?php\n" . $renderer->render($view))) {
            return false;
        }

        $fullClassName = sprintf('%s\Controller\\%s', $module, $className);
        $this->configResource->patch(array(
            'controllers' => array(
                'invokables' => array(
                    $controllerService => $fullClassName,
                ),
            ),
        ), true);

        return (object) array(
            'class'   => $fullClassName,
            'file'    => $classPath,
            'service' => $controllerService,
        );
    }

    /**
     * Create the route configuration
     * 
     * @param  string $route 
     * @param  string $serviceName 
     * @param  string $controllerService 
     * @return string The newly created route name
     */
    public function createRoute($route, $serviceName, $controllerService = null)
    {
        if (null === $controllerService) {
            $controllerService = sprintf('%s\Controller\\%s', $this->module, $serviceName);
        }

        $routeName = sprintf('%s.%s', $this->normalize($this->module), $this->normalize($serviceName));
        $action    = lcfirst($serviceName);

        $config = array('router' => array('routes' => array(
            $routeName => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => $route,
                    'defaults' => array(
                        'controller' => $controllerService,
                        'action'     => $action,
                    ),
                ),
            ),
        )));

        $this->configResource->patch($config, true);
        return $routeName;
    }

    /**
     * Create the zf-rpc configuration for the controller service
     * 
     * @param  string $controllerService 
     * @param  string $routeName 
     * @param  array $httpMethods 
     * @param  null|string|callable $callable 
     * @return array
     */
    public function createRpcConfig($controllerService, $routeName, array $httpMethods = array('GET'), $callable = null)
    {
        $config = array('zf-rpc' => array(
            $controllerService => array(
                'http_methods' => $httpMethods,
                'route_name'   => $routeName,
            ),
        ));
        if (null !== $callable) {
            $config[$controllerService]['callable'] = $callable;
        }
        return $this->configResource->patch($config, true);
    }

    /**
     * Create the selector configuration
     * 
     * @param  string $controllerService 
     * @param  string $selector 
     * @return array
     */
    public function createSelectorConfig($controllerService, $selector = null)
    {
        if (null === $selector) {
            $selector = 'Json';
        }

        $config = array('zf-content-negotiation' => array(
            'controllers' => array(
                $controllerService => $selector,
            ),
        ));
        return $this->configResource->patch($config, true);
    }

    /**
     * Update the route associated with a controller service
     * 
     * @param  string $controllerService 
     * @param  string $routeMatch 
     * @return true
     */
    public function updateRoute($controllerService, $routeMatch)
    {
        $endpoint  = $this->fetch($controllerService);
        if (!$endpoint) {
            return false;
        }
        $endpoint  = $endpoint->getArrayCopy();
        $routeName = $endpoint['route_name'];

        $config = $this->configResource->fetch(true);
        $config['router']['routes'][$routeName]['options']['route'] = $routeMatch;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Update the allowed HTTP methods for a given service
     * 
     * @param  string $controllerService 
     * @param  array $httpMethods 
     * @return true
     */
    public function updateHttpMethods($controllerService, array $httpMethods)
    {
        $config = $this->configResource->fetch(true);
        $config['zf-rpc'][$controllerService]['http_methods'] = $httpMethods;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Update the content-negotiation selector for the given service
     * 
     * @param  string $controllerService 
     * @param  string $selector 
     * @return true
     */
    public function updateSelector($controllerService, $selector)
    {
        $config = $this->configResource->fetch(true);
        $config['zf-content-negotiation']['controllers'][$controllerService] = $selector;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Normalize a service or module name to lowercase, dash-separated
     * 
     * @param  string $string 
     * @return string
     */
    protected function normalize($string)
    {
        $filter = $this->getNormalizationFilter();
        return $filter->filter($string);
    }

    /**
     * Retrieve and/or initialize the normalization filter chain
     * 
     * @return FilterChain
     */
    protected function getNormalizationFilter()
    {
        if ($this->filter instanceof FilterChain) {
            return $this->filter;
        }
        $this->filter = new FilterChain();
        $this->filter->attachByName('WordCamelCaseToDash')
                     ->attachByName('StringToLower');
        return $this->filter;
    }

    /**
     * Retrieve the URL match for the given route name
     * 
     * @param  string $routeName 
     * @param  array $config 
     * @return false|string
     */
    protected function getRouteMatchStringFromModuleConfig($routeName, array $config)
    {
        if (!isset($config['router'])
            || !isset($config['router']['routes'])
        ) {
            return false;
        }

        $config = $config['router']['routes'];
        if (!isset($config[$routeName])
            || !is_array($config[$routeName])
        ) {
            return false;
        }

        $config = $config[$routeName];

        if (!isset($config['options'])
            || !isset($config['options']['route'])
        ) {
            return false;
        }

        return $config['options']['route'];
    }
}
