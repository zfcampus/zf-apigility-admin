<?php

namespace ZF\ApiFirstAdmin;

use Zend\Mvc\MvcEvent;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\Resource;
use ZF\Hal\View\HalJsonModel;

class Module
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $app->getServiceManager();
        $events   = $app->getEventManager();
        $events->attach('render', array($this, 'onRender'), 100);
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getDiagnostics()
    {
        return array(
            'Config File Writable' => function () {
                if (!defined('APPLICATION_PATH')) {
                    return false;
                }
                if (!is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'ZF\ApiFirstAdmin\Model\ApiFirstModule' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\ApiFirstAdmin\Model\ApiFirstModule service because ModuleManager service is not present'
                    );
                }
                $modules    = $services->get('ModuleManager');
                $restConfig = array();
                $rpcConfig  = array();
                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['zf-rest'])) {
                        $restConfig = $config['zf-rest'];
                    }
                    if (isset($config['zf-rpc'])) {
                        $rpcConfig = $config['zf-rpc'];
                    }
                }
                return new Model\ApiFirstModule($modules, $restConfig, $rpcConfig);
            },
            'ZF\ApiFirstAdmin\Model\ApiFirstModuleListener' => function ($services) {
                $moduleModel = $services->get('ZF\ApiFirstAdmin\Model\ApiFirstModule');
                $listener    = new Model\ApiFirstModuleListener($moduleModel);

                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['zf-api-first-admin'])) {
                        if (isset($config['zf-api-first-admin']['module_path'])) {
                            $listener->setModulePath($config['zf-api-first-admin']['module_path']);
                        }
                    }
                }
                return $listener;
            },
            'ZF\ApiFirstAdmin\Model\CodeConnectedRestFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                ) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\CodeConnectedRestFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                return new Model\CodeConnectedRestFactory($moduleUtils, $configFactory);
            },
            'ZF\ApiFirstAdmin\Model\CodeConnectedRpcFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                ) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\CodeConnectedRpcFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                return new Model\CodeConnectedRpcFactory($moduleUtils, $configFactory);
            },
            'ZF\ApiFirstAdmin\Model\ApiFirstRestEndpointListener' => function ($services) {
                if (!$services->has('ZF\ApiFirstAdmin\Model\CodeConnectedRestFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\ApiFirstRestEndpointListener is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\ApiFirstAdmin\Model\CodeConnectedRestFactory');
                return new Model\ApiFirstRestEndpointListener($factory);
            },
            'ZF\ApiFirstAdmin\Model\ApiFirstRpcEndpointListener' => function ($services) {
                if (!$services->has('ZF\ApiFirstAdmin\Model\CodeConnectedRpcFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\ApiFirstRpcEndpointListener is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\ApiFirstAdmin\Model\CodeConnectedRpcFactory');
                return new Model\ApiFirstRpcEndpointListener($factory);
            },
        ));
    }

    public function getControllerConfig()
    {
        return array('factories' => array(
            'ZF\ApiFirstAdmin\Controller\Module' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('ZF\ApiFirstAdmin\Model\ApiFirstModule');
                return new Controller\ModuleController($model);
            },
        ));
    }

    /**
     * Inject links into Module resources for the service endpoints
     * 
     * @param  \Zend\Mvc\MvcEvent $e 
     */
    public function onRender($e)
    {
        $matches = $e->getRouteMatch();
        $controller = $matches->getParam('controller', false);
        if ($controller != 'ZF\ApiFirstAdmin\Controller\ModuleResource') {
            return;
        }

        $result = $e->getResult();
        if (!$result instanceof HalJsonModel) {
            return;
        }

        if ($result->isResource()) {
            $this->injectEndpointLinks($result->getPayload(), $result);
            return;
        }

        if ($result->isCollection()) {
            $viewHelpers = $this->sm->get('ViewHelperManager');
            $halPlugin   = $viewHelpers->get('hal');
            $halPlugin->getEventManager()->attach('renderCollection.resource', array($this, 'onRenderCollectionResource'), 10);
        }
    }

    /**
     * Inject links for the service endpoints of a module
     * 
     * @param  Resource $resource 
     * @param  HalJsonModel $model 
     */
    protected function injectEndpointLinks(Resource $resource, HalJsonModel $model)
    {
        $module = $resource->resource;
        $links  = $resource->getLinks();

        $this->injectLinksForEndpointsByType('rest', $module['rest'], $links);
        unset($module['rest']);

        $this->injectLinksForEndpointsByType('rpc', $module['rpc'], $links);
        unset($module['rpc']);

        $replacement = new Resource($module, $resource->id);
        $replacement->setLinks($links);
        $model->setPayload($replacement);
    }

    /**
     * Inject RPC/REST endpoint links inside module resources that are composed in collections
     * 
     * @param  \Zend\EventManager\Event $e 
     */
    public function onRenderCollectionResource($e)
    {
        $resource = $e->getParam('resource');
        if (!$resource instanceof Model\ModuleMetadata) {
            return;
        }

        $asArray = $resource->getArrayCopy();
        $module  = $asArray['name'];
        $rest    = $asArray['rest'];
        $rpc     = $asArray['rpc'];

        unset($asArray['rest']);
        unset($asArray['rpc']);

        $halResource = new Resource($asArray, $module);
        $links       = $halResource->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'zf-api-first-admin/api/module',
                'params' => array(
                    'name' => $module,
                ),
            ),
        )));
        $this->injectLinksForEndpointsByType('rest', $rest, $links, $module);
        $this->injectLinksForEndpointsByType('rpc', $rpc, $links, $module);

        $e->setParam('resource', $halResource);
    }

    /**
     * Inject endpoint links
     * 
     * @param  string $type "rpc" | "rest"
     * @param  array|\Traversable $endpoints 
     * @param  LinkCollection $links 
     * @param  null|string $module
     */
    protected function injectLinksForEndpointsByType($type, $endpoints, LinkCollection $links, $module = null)
    {
        $routeName = sprintf('zf-api-first-admin/api/module/%s-endpoint', $type);
        foreach ($endpoints as $name) {
            $spec = array(
                'rel' => $type,
                'route' => array(
                    'name' => $routeName,
                    'params' => array(
                        'controller_service_name' => $name,
                    ),
                ),
            );
            if (null !== $module) {
                $spec['route']['params']['name'] = $module;
            }
            $link = Link::factory($spec);
            $links->add($link);
        }
    }
}
