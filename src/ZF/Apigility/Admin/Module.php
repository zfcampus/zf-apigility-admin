<?php

namespace ZF\Apigility\Admin;

use Zend\Config\Writer\PhpArray as PhpArrayWriter;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use ZF\Configuration\ConfigResource;
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
        return include __DIR__ . '/../../../../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'ZF\Apigility\Admin\Model\DbAdapterModel' => function($services) {
                if (!$services->has('Config')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\Apigility\Admin\Model\DbAdapterModel service because Config service is not present'
                    );
                }
                $config = $services->get('Config');
                $writer = new PhpArrayWriter();

                $global = new ConfigResource($config, 'config/autoload/global.php', $writer);
                $local  = new ConfigResource($config, 'config/autoload/local.php', $writer);
                return new Model\DbAdapterModel($global, $local);
            },
            'ZF\Apigility\Admin\Model\DbAdapterResource' => function($services) {
                if (!$services->has('ZF\Apigility\Admin\Model\DbAdapterModel')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\Apigility\Admin\Model\DbAdapterResource service because ZF\Apigility\Admin\Model\DbAdapterModel service is not present'
                    );
                }
                $model = $services->get('ZF\Apigility\Admin\Model\DbAdapterModel');
                return new Model\DbAdapterResource($model);
            },
            'ZF\Apigility\Admin\Model\ModuleModel' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\Apigility\Admin\Model\ModuleModel service because ModuleManager service is not present'
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
            'ZF\Apigility\Admin\Model\ModuleResource' => function ($services) {
                $moduleModel = $services->get('ZF\Apigility\Admin\Model\ModuleModel');
                $listener    = new Model\ModuleResource($moduleModel);

                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['zf-apigility-admin'])) {
                        if (isset($config['zf-apigility-admin']['module_path'])) {
                            $listener->setModulePath($config['zf-apigility-admin']['module_path']);
                        }
                    }
                }
                return $listener;
            },
            'ZF\Apigility\Admin\Model\RestServiceModelFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                    || !$services->has('ZF\Apigility\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {;
                    throw new ServiceNotCreatedException(
                        'ZF\Apigility\Admin\Model\RestServiceModelFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleModel   = $services->get('ZF\Apigility\Admin\Model\ModuleModel');
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');

                // Wire DB-Connected fetch listener
                $sharedEvents->attach(__NAMESPACE__ . '\Model\RestServiceModel', 'fetch', 'ZF\Apigility\Admin\Model\DbConnectedRestServiceModel::onFetch');

                return new Model\RestServiceModelFactory($moduleUtils, $configFactory, $sharedEvents, $moduleModel);
            },
            'ZF\Apigility\Admin\Model\RpcServiceModelFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ModuleUtils')
                    || !$services->has('ZF\Configuration\ConfigResourceFactory')
                    || !$services->has('ZF\Apigility\Admin\Model\ModuleModel')
                    || !$services->has('SharedEventManager')
                ) {
                    throw new ServiceNotCreatedException(
                        'ZF\Apigility\Admin\Model\RpcServiceModelFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $moduleModel   = $services->get('ZF\Apigility\Admin\Model\ModuleModel');
                $moduleUtils   = $services->get('ZF\Configuration\ModuleUtils');
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                $sharedEvents  = $services->get('SharedEventManager');
                return new Model\RpcServiceModelFactory($moduleUtils, $configFactory, $sharedEvents, $moduleModel);
            },
            'ZF\Apigility\Admin\Model\RestServiceResource' => function ($services) {
                if (!$services->has('ZF\Apigility\Admin\Model\RestServiceModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\Apigility\Admin\Model\RestServiceResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\Apigility\Admin\Model\RestServiceModelFactory');
                return new Model\RestServiceResource($factory);
            },
            'ZF\Apigility\Admin\Model\RpcServiceResource' => function ($services) {
                if (!$services->has('ZF\Apigility\Admin\Model\RpcServiceModelFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\Apigility\Admin\Model\RpcServiceResource is missing one or more dependencies'
                    );
                }
                $factory = $services->get('ZF\Apigility\Admin\Model\RpcServiceModelFactory');
                return new Model\RpcServiceResource($factory);
            },
            'ZF\Apigility\Admin\Model\VersioningModelFactory' => function ($services) {
                if (!$services->has('ZF\Configuration\ConfigResourceFactory')) {
                    throw new ServiceNotCreatedException(
                        'ZF\Apigility\Admin\Model\VersioningModelFactory is missing one or more dependencies from ZF\Configuration'
                    );
                }
                $configFactory = $services->get('ZF\Configuration\ConfigResourceFactory');
                return new Model\VersioningModelFactory($configFactory);
            },
        ));
    }

    public function getControllerConfig()
    {
        return array('factories' => array(
            'ZF\Apigility\Admin\Controller\ModuleCreation' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('ZF\Apigility\Admin\Model\ModuleModel');
                return new Controller\ModuleCreationController($model);
            },
            'ZF\Apigility\Admin\Controller\Source' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $model    = $services->get('ZF\Apigility\Admin\Model\ModuleModel');
                return new Controller\SourceController($model);
            },
            'ZF\Apigility\Admin\Controller\Versioning' => function ($controllers) {
                $services = $controllers->getServiceLocator();
                $factory  = $services->get('ZF\Apigility\Admin\Model\VersioningModelFactory');
                return new Controller\VersioningController($factory);
            },
        ));
    }

    /**
     * Inject links into Module resources for the service services
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onRender($e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches instanceof RouteMatch) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        $controller = $matches->getParam('controller', false);
        if ($controller != 'ZF\Apigility\Admin\Controller\Module') {
            return;
        }

        $result = $e->getResult();
        if (!$result instanceof HalJsonModel) {
            return;
        }

        if ($result->isResource()) {
            $this->injectServiceLinks($result->getPayload(), $result);
            return;
        }

        if ($result->isCollection()) {
            $viewHelpers = $this->sm->get('ViewHelperManager');
            $halPlugin   = $viewHelpers->get('hal');
            $halPlugin->getEventManager()->attach('renderCollection.resource', array($this, 'onRenderCollectionResource'), 10);
        }
    }

    /**
     * Inject links for the service services of a module
     *
     * @param  Resource $resource
     * @param  HalJsonModel $model
     */
    protected function injectServiceLinks(Resource $resource, HalJsonModel $model)
    {
        $module     = $resource->resource;
        $links      = $resource->getLinks();
        $moduleName = $module['name'];
        $versions   = $module['versions'];

        $this->injectLinksForServicesByType('rest', $module['rest'], $versions, $links, $moduleName);
        unset($module['rest']);

        $this->injectLinksForServicesByType('rpc', $module['rpc'], $versions, $links, $moduleName);
        unset($module['rpc']);

        $replacement = new Resource($module, $resource->id);
        $replacement->setLinks($links);
        $model->setPayload($replacement);
    }

    /**
     * Inject RPC/REST service links inside module resources that are composed in collections
     *
     * @param  \Zend\EventManager\Event $e
     */
    public function onRenderCollectionResource($e)
    {
        $resource = $e->getParam('resource');
        if (!$resource instanceof Model\ModuleEntity) {
            return;
        }

        $asArray  = $resource->getArrayCopy();
        $module   = $asArray['name'];
        $versions = $asArray['versions'];
        $rest     = $asArray['rest'];
        $rpc      = $asArray['rpc'];

        unset($asArray['rest']);
        unset($asArray['rpc']);

        $halResource = new Resource($asArray, $module);
        $links       = $halResource->getLinks();
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => 'zf-apigility-admin/api/module',
                'params' => array(
                    'name' => $module,
                ),
            ),
        )));
        $this->injectLinksForServicesByType('rest', $rest, $versions, $links, $module);
        $this->injectLinksForServicesByType('rpc', $rpc, $versions, $links, $module);

        $e->setParam('resource', $halResource);
    }

    /**
     * Inject service links
     *
     * @param  string $type "rpc" | "rest"
     * @param  array|\Traversable $services
     * @param  array $versions
     * @param  LinkCollection $links
     * @param  null|string $module
     */
    protected function injectLinksForServicesByType($type, $services, array $versions, LinkCollection $links, $module = null)
    {
        if (empty($versions)) {
            $routeName = sprintf('zf-apigility-admin/api/module/%s-service', $type);
            $spec = array(
                'rel' => $type,
                'route' => array(
                    'name' => $routeName,
                ),
                'props' => array(
                    'latest' => true,
                ),
            );
            if (null !== $module) {
                $spec['route']['params']['name'] = $module;
            }
            $link = Link::factory($spec);
            $links->add($link);
            return;
        }

        $max = max($versions);
        foreach ($versions as $version) {
            $routeName = sprintf('zf-apigility-admin/api/module/%s-service', $type);
            $isLatest  = ($version === $max);
            $spec = array(
                'rel' => $type,
                'route' => array(
                    'name' => $routeName,
                    'options' => array(
                        'query' => array(
                            'version' => $version,
                        ),
                    ),
                ),
                'props' => array(
                    'version' => $version,
                    'latest'  => $isLatest,
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
