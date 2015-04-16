<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\FilterChain;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\Apigility\Admin\Exception;
use ZF\Apigility\Admin\Utility;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ModuleUtils;
use ZF\Rest\Exception\PatchException;
use ZF\Rest\Exception\CreationException;
use ReflectionClass;

class RpcServiceModel
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
     * @var ModuleEntity
     */
    protected $moduleEntity;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @param  string $module
     * @param  ModuleUtils $modules
     * @param  ConfigResource $config
     */
    public function __construct(ModuleEntity $moduleEntity, ModulePathSpec $modules, ConfigResource $config)
    {
        $this->module         = $moduleEntity->getName();
        $this->moduleEntity   = $moduleEntity;
        $this->modules        = $modules;
        $this->configResource = $config;
    }

    /**
     * Fetch a single RPC service
     *
     * @todo   get route details?
     * @param  string $controllerServiceName
     * @return RpcServiceEntity|false
     */
    public function fetch($controllerServiceName)
    {
        $data   = array('controller_service_name' => $controllerServiceName);
        $config = $this->configResource->fetch(true);

        if (!isset($config['zf-rpc'][$controllerServiceName])) {
            return false;
        }

        $rpcConfig = $config['zf-rpc'][$controllerServiceName];

        if (isset($rpcConfig['route_name'])) {
            $data['route_name']  = $rpcConfig['route_name'];
            $data['route_match'] = $this->getRouteMatchStringFromModuleConfig($data['route_name'], $config);
        }

        if (isset($rpcConfig['http_methods'])) {
            $data['http_methods'] = $rpcConfig['http_methods'];
        }

        if (isset($rpcConfig['service_name'])
            && !empty($rpcConfig['service_name'])
        ) {
            $data['service_name'] = $rpcConfig['service_name'];
        } else {
            $data['service_name'] = $controllerServiceName;
            $pattern = vsprintf(
                '#%sV[^%s]+%sRpc%s(?<service>[^%s]+)%sController#',
                array_fil(0, 6, preg_quote('\\'))
            );
            if (preg_match($pattern, $controllerServiceName, $matches)) {
                $data['service_name'] = $matches['service'];
            }
        }

        if (isset($config['zf-content-negotiation'])) {
            $contentNegotiationConfig = $config['zf-content-negotiation'];
            if (isset($contentNegotiationConfig['controllers'])
                && isset($contentNegotiationConfig['controllers'][$controllerServiceName])
            ) {
                $data['selector'] = $contentNegotiationConfig['controllers'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['accept_whitelist'])
                && isset($contentNegotiationConfig['accept_whitelist'][$controllerServiceName])
            ) {
                $data['accept_whitelist'] = $contentNegotiationConfig['accept_whitelist'][$controllerServiceName];
            }

            if (isset($contentNegotiationConfig['content_type_whitelist'])
                && isset($contentNegotiationConfig['content_type_whitelist'][$controllerServiceName])
            ) {
                $data['content_type_whitelist'] =
                    $contentNegotiationConfig['content_type_whitelist'][$controllerServiceName];
            }
        }

        $service = new RpcServiceEntity();
        $service->exchangeArray($data);
        return $service;
    }

    /**
     * Fetch all services
     *
     * @return RpcServiceEntity[]
     */
    public function fetchAll($version = null)
    {
        $config = $this->configResource->fetch(true);
        if (!isset($config['zf-rpc'])) {
            return array();
        }

        $services = array();
        $pattern  = false;

        // Initialize pattern if a version was passed and it's valid
        if (null !== $version) {
            if (!in_array($version, $this->moduleEntity->getVersions())) {
                throw new Exception\RuntimeException(sprintf(
                    'Invalid version "%s" provided',
                    $version
                ), 400);
            }
            $namespaceSep = preg_quote('\\');
            $pattern = sprintf(
                '#%s%sV%s#',
                $this->module,
                $namespaceSep,
                $version
            );
        }

        foreach (array_keys($config['zf-rpc']) as $controllerService) {
            if (!$pattern) {
                $services[] = $this->fetch($controllerService);
                continue;
            }

            if (preg_match($pattern, $controllerService)) {
                $services[] = $this->fetch($controllerService);
                continue;
            }
        }

        return $services;
    }

    /**
     * Create a new RPC service in this module
     *
     * Creates the controller and all configuration, returning the full configuration as a tree.
     *
     * @todo   Return the controller service name
     * @param  string $serviceName
     * @param  string $routeMatch
     * @param  array $httpMethods
     * @param  null|string $selector
     * @return RpcServiceEntity
     */
    public function createService($serviceName, $routeMatch, $httpMethods, $selector = null)
    {
        $normalizedServiceName = ucfirst($serviceName);

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\\\[a-zA-Z][a-zA-Z0-9_]*)*$/', $normalizedServiceName)) {
            throw new CreationException('Invalid service name; must be a valid PHP namespace name.');
        }

        $controllerData    = $this->createController($normalizedServiceName);
        $controllerService = $controllerData->service;
        $routeName         = $this->createRoute($routeMatch, $normalizedServiceName, $controllerService);
        $this->createRpcConfig($serviceName, $controllerService, $routeName, $httpMethods);
        $this->createContentNegotiationConfig($controllerService, $selector);

        return $this->fetch($controllerService);
    }

    /**
     * Delete a service
     *
     * @param  RpcServiceEntity $entity
     * @param  bool $recursive
     * @return true
     */
    public function deleteService(RpcServiceEntity $entity, $recursive = false)
    {
        $serviceName = $entity->controllerServiceName;
        $routeName   = $entity->routeName;

        $this->deleteRouteConfig($routeName, $serviceName);
        $this->deleteRpcConfig($serviceName);
        $this->deleteContentNegotiationConfig($serviceName);
        $this->deleteContentValidationConfig($serviceName);
        $this->deleteVersioningConfig($routeName, $serviceName);
        $this->deleteAuthorizationConfig($serviceName);
        $this->deleteControllersConfig($serviceName);

        if ($recursive) {
            $className = substr($entity->controllerServiceName, 0, strrpos($entity->controllerServiceName, '\\')) .
                '\\' . $entity->serviceName . 'Controller';
            if (!class_exists($className)) {
                throw new Exception\RuntimeException(sprintf(
                    'I cannot determine the class name, tried with "%s"',
                    $className
                ), 400);
            }
            $reflection = new ReflectionClass($className);
            Utility::recursiveDelete(dirname($reflection->getFileName()));
        }
        return true;
    }

    public function createFactoryController($serviceName)
    {
        $module     = $this->module;
        $version    = $this->moduleEntity->getLatestVersion();

        $srcPath = $this->modules->getRpcPath($module, $version, $serviceName);

        $className         = sprintf('%sController', $serviceName);
        $classFactory      = sprintf('%sControllerFactory', $serviceName);
        $classPath         = sprintf('%s/%s.php', $srcPath, $classFactory);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller factory "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel(array(
                'module'       => $module,
                'classname'    => $className,
                'classfactory' => $classFactory,
                'servicename'  => $serviceName,
                'version'      => $version,
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
                'code-connected/rpc-controller' => __DIR__ . '/../../view/code-connected/rpc-factory.phtml'
        ));

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents(
            $classPath,
            "<" . "?php\n" . $renderer->render($view)
        )) {
            return false;
        }

        return sprintf('%s\\V%s\\Rpc\\%s\\%s', $module, $version, $serviceName, $classFactory);
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
        $version    = $this->moduleEntity->getLatestVersion();
        $serviceName = str_replace("\\", "/", $serviceName);

        $srcPath = $this->modules->getRpcPath($module, $version, $serviceName);

        if (!file_exists($srcPath)) {
            mkdir($srcPath, 0775, true);
        }

        $className         = sprintf('%sController', $serviceName);
        $classPath         = sprintf('%s/%s.php', $srcPath, $className);
        $controllerService = sprintf('%s\\V%s\\Rpc\\%s\\Controller', $module, $version, $serviceName);

        if (file_exists($classPath)) {
            throw new Exception\RuntimeException(sprintf(
                'The controller "%s" already exists',
                $className
            ));
        }

        $view = new ViewModel(array(
            'module'      => $module,
            'classname'   => $className,
            'servicename' => $serviceName,
            'version'     => $version,
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
            'code-connected/rpc-controller' => __DIR__ . '/../../view/code-connected/rpc-controller.phtml'
        ));

        $view->setTemplate('code-connected/rpc-controller');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents(
            $classPath,
            "<" . "?php\n" . $renderer->render($view)
        )) {
            return false;
        }

        $fullClassFactory = $this->createFactoryController($serviceName);

        $this->configResource->patch(array(
            'controllers' => array(
                'factories' => array(
                    $controllerService => $fullClassFactory,
                ),
            ),
        ), true);

        $fullClassName = sprintf('%s\\V%s\\Rpc\\%s\\%s', $module, $version, $serviceName, $className);

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
            $controllerService = sprintf('%s\\Rpc\\%s\\Controller', $this->module, $serviceName);
        }

        $routeName = sprintf('%s.rpc.%s', $this->normalize($this->module), $this->normalize($serviceName));
        $action    = lcfirst($serviceName);

        $config = array(
            'router' => array(
                'routes' => array(
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
                )
            ),
            'zf-versioning' => array(
                'uri' => array(
                    $routeName
                )
            )
        );

        $this->configResource->patch($config, true);
        return $routeName;
    }

    /*
     * Create the zf-rpc configuration for the controller service
     *
     * @param  string $serviceName
     * @param  string $controllerService
     * @param  string $routeName
     * @param  array $httpMethods
     * @param  null|string|callable $callable
     * @return array
     */
    public function createRpcConfig(
        $serviceName,
        $controllerService,
        $routeName,
        array $httpMethods = array('GET'),
        $callable = null
    ) {
        $config = array('zf-rpc' => array(
            $controllerService => array(
                'service_name' => $serviceName,
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
    public function createContentNegotiationConfig($controllerService, $selector = null)
    {
        if (null === $selector) {
            $selector = 'Json';
        }

        $mediaType = $this->createMediaType();

        $config = array('zf-content-negotiation' => array(
            'controllers' => array(
                $controllerService => $selector,
            ),
            'accept_whitelist' => array(
                $controllerService => array(
                    $mediaType,
                    'application/json',
                    'application/*+json',
                ),
            ),
            'content_type_whitelist' => array(
                $controllerService => array(
                    $mediaType,
                    'application/json',
                ),
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
        $services  = $this->fetch($controllerService);
        if (!$services) {
            return false;
        }
        $services  = $services->getArrayCopy();
        $routeName = $services['route_name'];

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
     * Update configuration for a content negotiation whitelist for a named controller service
     *
     * @param  string $controllerService
     * @param  string $headerType
     * @param  array $whitelist
     * @return true
     */
    public function updateContentNegotiationWhitelist($controllerService, $headerType, array $whitelist)
    {
        if (!in_array($headerType, array('accept', 'content_type'))) {
            /** @todo define exception in Rpc namespace */
            throw new PatchException('Invalid content negotiation whitelist type provided', 422);
        }
        $headerType .= '_whitelist';
        $config = $this->configResource->fetch(true);
        $config['zf-content-negotiation'][$headerType][$controllerService] = $whitelist;
        $this->configResource->overwrite($config);
        return true;
    }

    /**
     * Removes the route configuration for a named route
     *
     * @param  string $routeName
     * @param  string $serviceName
     */
    public function deleteRouteConfig($routeName, $serviceName)
    {
        if (false === strstr($serviceName, '\\V1\\')) {
            // > V1; nothing to do
            return;
        }

        $key = array('router', 'routes', $routeName);
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete any versionin configuration for a service
     *
     * Only for version 1; later versions will do nothing
     *
     * @param  string $routeName
     * @param  string $serviceName
     */
    public function deleteVersioningConfig($routeName, $serviceName)
    {
        if (false === strstr($serviceName, '\\V1\\')) {
            // > V1; nothing to do
            return;
        }

        $config = $this->configResource->fetch(true);
        if (! isset($config['zf-versioning']['uri'])) {
            return;
        }

        if (! in_array($routeName, $config['zf-versioning']['uri'], true)) {
            return;
        }

        $versioning = array_filter($config['zf-versioning']['uri'], function ($value) use ($routeName) {
            if ($routeName === $value) {
                return false;
            }
            return true;
        });

        $key = array('zf-versioning', 'uri');
        $this->configResource->patchKey($key, $versioning);
    }

    /**
     * Remove any controller service configuration for a service
     *
     * @param  string $serviceName
     */
    public function deleteControllersConfig($serviceName)
    {
        foreach (array('invokables', 'factories') as $serviceType) {
            $key = array('controllers', $serviceType, $serviceName);
            $this->configResource->deleteKey($key);
        }
    }

    /**
     * Delete the RPC configuration for a named RPC service
     *
     * @param  string $serviceName
     */
    public function deleteRpcConfig($serviceName)
    {
        $key = array('zf-rpc', $serviceName);
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete the Content Negotiation configuration for a named RPC
     * service
     *
     * @param  string $serviceName
     */
    public function deleteContentNegotiationConfig($serviceName)
    {
        $key = array('zf-content-negotiation', 'controllers', $serviceName);
        $this->configResource->deleteKey($key);

        $key = array('zf-content-negotiation', 'accept_whitelist', $serviceName);
        $this->configResource->deleteKey($key);

        $key = array('zf-content-negotiation', 'content_type_whitelist', $serviceName);
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete content-validation configuration associated with a service
     *
     * @param  string $serviceName
     */
    public function deleteContentValidationConfig($serviceName)
    {
        $key = array('zf-content-validation', $serviceName);
        $this->configResource->deleteKey($key);
    }

    /**
     * Delete authorization configuration associated with a service
     *
     * @param  string $serviceName
     */
    public function deleteAuthorizationConfig($serviceName)
    {
        $key = array('zf-mvc-auth', 'authorization', $serviceName);
        $this->configResource->deleteKey($key);
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
        $string = str_replace('\\', '-', $string);
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

    /**
     * Create the mediatype for this
     *
     * Based on the module and the latest module version.
     *
     * @return string
     */
    public function createMediaType()
    {
        return sprintf(
            'application/vnd.%s.v%s+json',
            $this->normalize($this->module),
            $this->moduleEntity->getLatestVersion()
        );
    }
}
