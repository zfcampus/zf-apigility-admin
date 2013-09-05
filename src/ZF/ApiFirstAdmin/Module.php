<?php

namespace ZF\ApiFirstAdmin;

use Zend\Mvc\MvcEvent;
use ZF\Hal\Link\Link;
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
            $this->injectEndpointLinks($result->getPayload());
            return;
        }
    }

    protected function injectEndpointLinks(Resource $resource)
    {
        $module = $resource->resource;
        $links  = $resource->getLinks();

        foreach ($module->getRestEndpoints() as $name) {
            $link = Link::factory(array(
                'rel' => 'rest',
                'route' => array(
                    'name' => 'zf-api-first-admin/api/module/rest-endpoint',
                    'params' => array(
                        'controller_service_name' => $name,
                    ),
                ),
            ));
            $links->add($link);
        }

        foreach ($module->getRpcEndpoints() as $name) {
            $link = Link::factory(array(
                'rel' => 'rpc',
                'route' => array(
                    'name' => 'zf-api-first-admin/api/module/rpc-endpoint',
                    'params' => array(
                        'controller_service_name' => $name,
                    ),
                ),
            ));
            $links->add($link);
        }
    }
}
