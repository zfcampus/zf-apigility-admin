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
            'ZF\ApiFirstAdmin\Model\ModuleModel' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\ApiFirstAdmin\Model\ModuleModel service because ModuleManager service is not present'
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
                return new Model\ModuleModel($modules, $restConfig, $rpcConfig);
            },
            'ZF\ApiFirstAdmin\Model\ModuleResource' => function ($services) {
                $moduleModel = $services->get('ZF\ApiFirstAdmin\Model\ModuleModel');
                $listener    = new Model\ModuleResource($moduleModel);

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
            'ZF\ApiFirstAdmin\Model\RestEndpointModelFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                ) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\RestEndpointModelFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                return new Model\RestEndpointModelFactory($moduleUtils, $configFactory);
            },
            'ZF\ApiFirstAdmin\Model\RpcEndpointModelFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                ) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\RpcEndpointModelFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                return new Model\RpcEndpointModelFactory($moduleUtils, $configFactory);
            },
            'ZF\ApiFirstAdmin\Model\RestEndpointResource' => function ($services) {
                if (!$services->has('ZF\ApiFirstAdmin\Model\RestEndpointModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\RestEndpointResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\ApiFirstAdmin\Model\RestEndpointModelFactory');
                return new Model\RestEndpointResource($factory);
            },
            'ZF\ApiFirstAdmin\Model\RpcEndpointResource' => function ($services) {
                if (!$services->has('ZF\ApiFirstAdmin\Model\RpcEndpointModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\ApiFirstAdmin\Model\RpcEndpointResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\ApiFirstAdmin\Model\RpcEndpointModelFactory');
                return new Model\RpcEndpointResource($factory);
            },
        ));
    }

    public function getControllerConfig()
    {
        return array('factories' => array(
            'ZF\ApiFirstAdmin\Controller\ModuleCreation' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('ZF\ApiFirstAdmin\Model\ModuleModel');
                return new Controller\ModuleCreationController($model);
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
        if ($controller != 'ZF\ApiFirstAdmin\Controller\Module') {
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
        if (!$resource instanceof Model\Module) {
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
        if (count($endpoints) < 1) {
            return;
        }
        $routeName = sprintf('zf-api-first-admin/api/module/%s-endpoint', $type);
        $spec = array(
            'rel' => $type,
            'route' => array(
                'name' => $routeName,
            ),
        );
        if (null !== $module) {
            $spec['route']['params']['name'] = $module;
        }
        $link = Link::factory($spec);
        $links->add($link);
    }
}
