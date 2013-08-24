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
     * Create a new RPC service in this module
     *
     * Creates the controller and all configuration, returning the full configuration as a tree.
     *
     * @param  string $serviceName 
     * @param  string $route 
     * @param  array $httpMethods 
     * @param  null|string $selector
     * @return array
     */
    public function createService($serviceName, $route, $httpMethods, $selector = null)
    {
        $controllerData = $this->createController($serviceName);
        $controllerService = $controllerData->service;
        $routeName      = $this->createRoute($route, $serviceName, $controllerService);
        $this->createRpcConfig($controllerService, $routeName, $httpMethods);
        $this->createSelectorConfig($controllerService, $selector);
        return $this->configResource->fetch(true);
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
}
