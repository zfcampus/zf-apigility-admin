<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin;

use Zend\Http\Header\GenericHeader;
use Zend\Http\Header\GenericMultiHeader;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\RouteMatch;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\Entity;
use ZF\Hal\View\HalJsonModel;

class Module
{
    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var callable
     */
    protected $urlHelper;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    /**
     * Disable OP-Cache
     *
     * @param ModuleManagerInterface $modules
     */
    public function init(ModuleManagerInterface $modules)
    {
        $this->disableOpCache();
    }

    /**
     * Ensure the UI module is loaded
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return ['ZF\Apigility\Admin\Ui'];
    }

    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $services = $app->getServiceManager();
        $events   = $app->getEventManager();

        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\NormalizeMatchedControllerServiceNameListener::class),
            -20
        );
        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'normalizeMatchedInputFilterName'], -20);
        $events->attach(MvcEvent::EVENT_ROUTE, [$this, 'onRoute'], -1000);
        $events->attach(MvcEvent::EVENT_RENDER, [$this, 'onRender'], 100);
        $events->attach(MvcEvent::EVENT_FINISH, [$this, 'onFinish'], 1000);
        $this->sm->get(Listener\CryptFilterListener::class)->attach($events);
    }

    /**
     * Run diagnostics
     *
     * @return array|bool
     */
    public function getDiagnostics()
    {
        return [
            'Config File Writable' => function () {
                if (! defined('APPLICATION_PATH')) {
                    return false;
                }
                if (! is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        ];
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function normalizeMatchedControllerServiceName($e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('controller_service_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('controller_service_name');
        $matches->setParam('controller_service_name', str_replace('-', '\\', $controller));
    }

    public function normalizeMatchedInputFilterName($e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('input_filter_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('input_filter_name');
        $matches->setParam('input_filter_name', str_replace('-', '\\', $controller));
    }

    /**
     * Ensure the render_collections flag of the HAL view helper is enabled
     * regardless of the configuration setting if we match an admin service.
     *
     * @param MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches
            || 0 !== strpos($matches->getParam('controller'), 'ZF\Apigility\Admin\\')
        ) {
            return;
        }

        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $helpers  = $services->get('ViewHelperManager');
        $hal      = $helpers->get('Hal');
        $hal->setRenderCollections(true);
    }

    /**
     * Inject links into Module resources for the service services
     *
     * @param MvcEvent $e
     */
    public function onRender(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! ($matches instanceof RouteMatch || $matches instanceof V2RouteMatch)) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        $result = $e->getResult();
        if (! $result instanceof HalJsonModel) {
            return;
        }

        $viewHelpers = $this->sm->get('ViewHelperManager');
        $halPlugin = $viewHelpers->get('hal');
        $this->initializeUrlHelper();

        $halPlugin->getEventManager()->attach(
            ['renderCollection', 'renderEntity', 'renderCollection.Entity'],
            function ($e) use ($matches) {
                if ($matches->getParam('controller_service_name')) {
                    $matches->setParam(
                        'controller_service_name',
                        str_replace('\\', '-', $matches->getParam('controller_service_name'))
                    );
                }
            }
        );

        // If content is empty, then send the response with a 204 and an emtpy body

        if ($result->isEntity()) {
            $this->injectServiceLinks($result->getPayload(), $result, $e);
            $halPlugin->getEventManager()->attach('renderEntity', [$this, 'onRenderEntity'], 10);
            return;
        }

        if ($result->isCollection()) {
            $this->mvcEvent = $e;
            $halPlugin->getEventManager()->attach(
                'renderCollection.entity',
                [$this, 'onRenderCollectionEntity'],
                10
            );
        }
    }

    /**
     * Tell browsers not to cache responses from the admin API
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onFinish($e)
    {
        $matches = $e->getRouteMatch();
        if (! ($matches instanceof RouteMatch || $matches instanceof V2RouteMatch)) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        if (! $matches->getParam('is_apigility_admin_api', false)) {
            // Not part of the Apigility Admin API; nothing to do
            return;
        }

        $request = $e->getRequest();
        if ($request->isGet() || $request->isHead()) {
            $this->disableHttpCache($e->getResponse()->getHeaders());
        }
    }

    protected function initializeUrlHelper()
    {
        $viewHelpers     = $this->sm->get('ViewHelperManager');
        $urlHelper       = $viewHelpers->get('Url');
        $serverUrlHelper = $viewHelpers->get('ServerUrl');

        $this->urlHelper = function (
            $routeName,
            $routeParams,
            $routeOptions,
            $reUseMatchedParams
        ) use (
            $urlHelper,
            $serverUrlHelper
        ) {
            $url = call_user_func(
                $urlHelper,
                $routeName,
                $routeParams,
                $routeOptions,
                $reUseMatchedParams
            );

            if (substr($url, 0, 4) == 'http') {
                return $url;
            }
            return call_user_func($serverUrlHelper, $url);
        };
    }

    /**
     * Inject links for the service services of a module
     *
     * @param Entity $halEntity
     * @param HalJsonModel $model
     * @param $e
     */
    protected function injectServiceLinks(Entity $halEntity, HalJsonModel $model, $e)
    {
        $entity = $halEntity->entity;
        $links  = $halEntity->getLinks();
        if ($entity instanceof Model\ModuleEntity) {
            $this->injectModuleResourceRelationalLinks($entity, $links, $model);
        }
        if ($entity instanceof Model\RestServiceEntity || $entity instanceof Model\RpcServiceEntity) {
            $this->normalizeEntityControllerServiceName($entity, $links, $model);
        }
        if ($entity instanceof Model\InputFilterEntity) {
            $this->normalizeEntityInputFilterName($entity, $links, $model);
        }
    }

    /**
     * @param Model\ModuleEntity $module
     * @param $links
     * @param HalJsonModel $model
     */
    protected function injectModuleResourceRelationalLinks(Model\ModuleEntity $module, $links, HalJsonModel $model)
    {
        $moduleData = $module->getArrayCopy();
        $moduleName = $moduleData['name'];

        $this->injectLinksForServicesByType('authorization', [], $links, $moduleName);

        $this->injectLinksForServicesByType('rest', $moduleData['rest'], $links, $moduleName);
        unset($moduleData['rest']);

        $this->injectLinksForServicesByType('rpc', $moduleData['rpc'], $links, $moduleName);
        unset($moduleData['rpc']);

        $module = new Model\ModuleEntity($module->getNamespace(), [], [], $module->isVendor());
        $module->exchangeArray($moduleData);
        $replacement = new Entity($module, $moduleName);
        $replacement->setLinks($links);
        $model->setPayload($replacement);
    }

    /**
     * @param $entity
     * @param $links
     * @param HalJsonModel $model
     */
    protected function normalizeEntityControllerServiceName($entity, $links, HalJsonModel $model)
    {
        $entity->exchangeArray([
            'controller_service_name' => str_replace('\\', '-', $entity->controllerServiceName),
        ]);
        $halEntity = new Entity($entity, $entity->controllerServiceName);

        if ($links->has('self')) {
            $links->remove('self');
        }
        $halEntity->setLinks($links);

        $model->setPayload($halEntity);
    }

    /**
     * @param Model\InputFilterEntity $entity
     * @param $links
     * @param HalJsonModel $model
     */
    protected function normalizeEntityInputFilterName(Model\InputFilterEntity $entity, $links, HalJsonModel $model)
    {
        $entity['input_filter_name'] = str_replace('\\', '-', $entity['input_filter_name']);
        $halEntity = new Entity($entity, $entity['input_filter_name']);

        if ($links->has('self')) {
            $links->remove('self');
        }
        $halEntity->setLinks($links);

        $model->setPayload($halEntity);
    }

    /**
     * @param $e
     */
    public function onRenderEntity($e)
    {
        $halEntity = $e->getParam('entity');
        $entity    = $halEntity->entity;
        $hal       = $e->getTarget();

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
            || (is_array($entity) && array_key_exists('controller_service_name', $entity))
        ) {
            $serviceName = is_array($entity) ? $entity['controller_service_name'] : $entity->controllerServiceName;
            $links       = $halEntity->getLinks();

            if ($links->has('input_filter')) {
                $link   = $links->get('input_filter');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, [
                    'controller_service_name' => $serviceName,
                ]));
            }

            if ($links->has('documentation')) {
                $link   = $links->get('documentation');
                $params = $link->getRouteParams();
                $link->setRouteParams(array_merge($params, [
                    'controller_service_name' => $serviceName,
                ]));
            }

            if (! $links->has('self')) {
                $route  = 'zf-apigility/api/module/';
                $route .= $entity instanceof Model\RestServiceEntity ? 'rest-service' : 'rpc-service';
                $hal->injectSelfLink($halEntity, $route, 'controller_service_name');
            }
            return;
        }

        if ($entity instanceof Model\InputFilterEntity
            || (is_array($entity) && isset($entity['input_filter_name']))
        ) {
            switch (true) {
                case ($entity instanceof Model\RestInputFilterEntity):
                    $type = 'rest-service';
                    break;
                case ($entity instanceof Model\RpcInputFilterEntity):
                    $type = 'rpc-service';
                    break;
            }
            $links = $halEntity->getLinks();

            if (! $links->has('self')) {
                $route = sprintf('zf-apigility/api/module/%s/input-filter', $type);
                $hal->injectSelfLink($halEntity, $route, 'input_filter_name');
            }
            return;
        }
    }

    /**
     * Inject links into collections
     *
     * Currently:
     *
     * - Inject RPC/REST service links inside module resources that are
     *   composed in collections
     *
     * @param  \Zend\EventManager\Event $e
     */
    public function onRenderCollectionEntity($e)
    {
        $entity = $e->getParam('entity');
        if ($entity instanceof Model\ModuleEntity) {
            return $this->injectModuleCollectionRelationalLinks($entity, $e);
        }

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
        ) {
            return $this->injectServiceCollectionRelationalLinks($entity, $e);
        }

        if ($entity instanceof Model\InputFilterEntity) {
            return $this->normalizeInputFilterEntityName($entity, $e);
        }
    }

    /**
     * Inject relational links into a Module resource
     *
     * @param Model\ModuleEntity $resource
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function injectModuleCollectionRelationalLinks(Model\ModuleEntity $resource, $e)
    {
        $asArray = $resource->getArrayCopy();
        $module  = $asArray['name'];
        $rest    = $asArray['rest'];
        $rpc     = $asArray['rpc'];

        unset($asArray['rest']);
        unset($asArray['rpc']);

        $halEntity = new Entity($asArray, $module);
        $links     = $halEntity->getLinks();
        $links->add(Link::factory([
            'rel' => 'self',
            'route' => [
                'name' => 'zf-apigility/api/module',
                'params' => [
                    'name' => $module,
                ],
            ],
        ]));
        $this->injectLinksForServicesByType('authorization', [], $links, $module);
        $this->injectLinksForServicesByType('rest', $rest, $links, $module);
        $this->injectLinksForServicesByType('rpc', $rpc, $links, $module);

        $e->setParam('entity', $halEntity);
    }

    /**
     * @param $entity
     * @param $e
     */
    public function injectServiceCollectionRelationalLinks($entity, $e)
    {
        $entity->exchangeArray([
            'controller_service_name' => str_replace('\\', '-', $entity->controllerServiceName),
        ]);

        $module  = $this->mvcEvent->getRouteMatch()->getParam('name');
        $service = $entity->controllerServiceName;
        $type    = $this->getServiceType($service);

        $halEntity = new Entity($entity, $service);
        $links = $halEntity->getLinks();

        // Need to inject the self relational link, as otherwise the HAL plugin
        // sees we have links, and does not inject one.
        $links->add(Link::factory([
            'rel' => 'self',
            'route' => [
                'name' => sprintf('zf-apigility/api/module/%s-service', $type),
                'params' => [
                    'name' => $module,
                    'controller_service_name' => $service,
                ],
            ],
        ]));

        // Add the input_filter relational link
        $links->add(Link::factory([
            'rel' => 'input_filter',
            'route' => [
                'name' => sprintf('zf-apigility/api/module/%s-service/input-filter', $type),
                'params' => [
                    'name' => $module,
                    'controller_service_name' => $service,
                ],
            ],
        ]));

        // Add the documentation relational link
        $links->add(Link::factory([
            'rel' => 'documentation',
            'route' => [
                'name' => sprintf('zf-apigility/api/module/%s-service/doc', $type),
                'params' => [
                    'name' => $module,
                    'controller_service_name' => $service,
                ],
            ],
        ]));

        $e->setParam('entity', $halEntity);
    }

    /**
     * @param $entity
     * @param $e
     */
    protected function normalizeInputFilterEntityName($entity, $e)
    {
        $entity['input_filter_name'] = str_replace('\\', '-', $entity['input_filter_name']);
        $e->setParam('entity', $entity);
    }

    /**
     * Inject service links
     *
     * @param  string $type "rpc" | "rest" | "authorization"
     * @param  array|\Traversable $services
     * @param  LinkCollection $links
     * @param  null|string $module
     */
    protected function injectLinksForServicesByType($type, $services, LinkCollection $links, $module = null)
    {
        $urlHelper = $this->urlHelper;

        $linkType = $type;
        if (in_array($type, ['rpc', 'rest'])) {
            $linkType .= '-service';
        }
        $routeName    = sprintf('zf-apigility/api/module/%s', $linkType);

        $routeParams  = [];
        if (null !== $module) {
            $routeParams['name'] = $module;
        }

        $routeOptions = [];

        $url  = call_user_func($urlHelper, $routeName, $routeParams, $routeOptions, false);
        $url .= '{?version}';

        $spec = [
            'rel'   => $type,
            'url'   => $url,
            'props' => [
                'templated' => true,
            ],
        ];

        $link = Link::factory($spec);
        $links->add($link);
    }

    /**
     * @param $service
     * @return string
     */
    protected function getServiceType($service)
    {
        if (preg_match('#[\\-.]Rest[\\-.]#', $service)) {
            return 'rest';
        }
        return 'rpc';
    }

    /**
     * Disable opcode caching
     *
     * Disables opcode caching for opcode caches that allow doing so during
     * runtime; the admin API will not work with opcode caching enabled.
     */
    protected function disableOpCache()
    {
        if (isset($_SERVER['SERVER_SOFTWARE'])
            && preg_match('/^PHP .*? Development Server$/', $_SERVER['SERVER_SOFTWARE'])
        ) {
            // skip the built-in PHP webserver (OPcache reset is not needed +
            // it crashes the server in PHP 5.4 with ZendOptimizer+)
            return;
        }

        // Disable opcode caches that allow runtime disabling

        if (function_exists('xcache_get')) {
            // XCache; just disable it
            ini_set('xcache.cacher', '0');
            return;
        }

        if (function_exists('wincache_ocache_meminfo')) {
            // WinCache; just disable it
            ini_set('wincache.ocenabled', '0');
            return;
        }
    }

    /**
     * Prepare cache-busting headers for GET requests
     *
     * Invoked from the onFinish() method for GET requests to disable client-side HTTP caching.
     *
     * @param \Zend\Http\Headers $headers
     */
    protected function disableHttpCache($headers)
    {
        $headers->addHeader(new GenericHeader('Expires', '0'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'no-store, no-cache, must-revalidate'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'post-check=0, pre-check=0'));
        $headers->addHeaderLine('Pragma', 'no-cache');
    }
}
