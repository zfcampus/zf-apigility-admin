<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use Interop\Container\ContainerInterface;
use Zend\EventManager\EventInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\RouteMatch;
use ZF\Apigility\Admin\Model;
use ZF\Hal\Entity;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\View\HalJsonModel;

class InjectModuleResourceLinksListener
{
    /**
     * @param RouteMatch|V2RouteMatch
     */
    private $routeMatch;

    /**
     * @param callable
     */
    private $urlHelper;

    /**
     * @var ContainerInterface
     */
    private $viewHelpers;

    /**
     * @param ContainerInterface $viewHelpers
     */
    public function __construct(ContainerInterface $viewHelpers)
    {
        $this->viewHelpers = $viewHelpers;
    }

    /**
     * Listen and respond to a render event.
     *
     * Does the following:
     * - Initializes a custom url helper for internal use
     * - attaches its "onHalRenderEvents" listener to the HAL plugin's
     *   renderCollection, renderEntity, and renderCollection.entity events.
     * - If the MvcEvent result's HalJsonModel composes an entity, injects
     *   service links into it, and attaches its "onRenderEntity" listener
     *   to the HAL plugin's renderEntity event.
     * - If the MvcEvent result's HalJsonModel composes a collection, memoizes
     *   the MvcEvent route match, and attaches its "onRenderCollectionEntity"
     *   listener to the HAL plugin's renderCollection.entity event.
     * @param MvcEvent $e
     * @return void
     */
    public function __invoke(MvcEvent $e)
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

        $halPlugin = $this->viewHelpers->get('Hal');
        $this->initializeUrlHelper();

        foreach (['renderCollection', 'renderEntity', 'renderCollection.entity'] as $event) {
            $halPlugin->getEventManager()->attach($event, [$this, 'onHalRenderEvents']);
        }

        // If content is empty, then send the response with a 204 and an emtpy body

        if ($result->isEntity()) {
            $this->injectServiceLinks($result->getPayload(), $result, $e);
            $halPlugin->getEventManager()->attach('renderEntity', [$this, 'onRenderEntity'], 10);
            return;
        }

        if ($result->isCollection()) {
            $this->routeMatch = $matches;
            $halPlugin->getEventManager()->attach(
                'renderCollection.entity',
                [$this, 'onRenderCollectionEntity'],
                10
            );
        }
    }

    /**
     * Normalize the route match controller service name.
     *
     * On each HAL plugin render event, if we have a route match containing
     * a controller service name, normalize it.
     *
     * @param EventInterface $e
     * return void
     */
    public function onHalRenderEvents(EventInterface $e)
    {
        if (! $this->routeMatch
            || ! $this->routeMatch->getParam('controller_service_name')
        ) {
            return;
        }

        $this->routeMatch->setParam(
            'controller_service_name',
            str_replace('\\', '-', $this->routeMatch->getParam('controller_service_name'))
        );
    }

    /**
     * Inject service entities with expected relational links.
     *
     * @param EventInterface $e
     * @return void
     */
    public function onRenderEntity(EventInterface $e)
    {
        $halEntity = $e->getParam('entity');
        $entity    = $halEntity->getEntity();
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
                    // fall-through
                default:
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
     * @param EventInterface $e
     * @return void
     */
    public function onRenderCollectionEntity(EventInterface $e)
    {
        $entity = $e->getParam('entity');
        if ($entity instanceof Model\ModuleEntity) {
            $this->injectModuleCollectionRelationalLinks($entity, $e);
            return;
        }

        if ($entity instanceof Model\RestServiceEntity
            || $entity instanceof Model\RpcServiceEntity
        ) {
            $this->injectServiceCollectionRelationalLinks($entity, $e);
            return;
        }

        if ($entity instanceof Model\InputFilterEntity) {
            $this->normalizeInputFilterEntityName($entity, $e);
            return;
        }
    }

    /**
     * Inject relational links into a Module resource
     *
     * @param Model\ModuleEntity $resource
     * @param EventInterface $e
     * @return void
     */
    private function injectModuleCollectionRelationalLinks(Model\ModuleEntity $resource, EventInterface $e)
    {
        $asArray = $resource->getArrayCopy();
        $module  = $asArray['name'];

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
        $this->injectLinksByType('authorization', $links, $module);
        $this->injectLinksByType('rest', $links, $module);
        $this->injectLinksByType('rpc', $links, $module);

        $e->setParam('entity', $halEntity);
    }

    /**
     * @param Model\RestServiceEntity|Model\RpcServiceEntity $entity
     * @param EventInterface $e
     * @return void
     */
    private function injectServiceCollectionRelationalLinks($entity, EventInterface $e)
    {
        $entity->exchangeArray([
            'controller_service_name' => str_replace('\\', '-', $entity->controllerServiceName),
        ]);

        $module  = $this->routeMatch->getParam('name');
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
     * Initializes the URL view helper to use when injecting links.
     *
     * Creates a helper that combines the functionality of the normal
     * url and serverUrl helpers.
     *
     * @return void
     */
    private function initializeUrlHelper()
    {
        $urlHelper       = $this->viewHelpers->get('Url');
        $serverUrlHelper = $this->viewHelpers->get('ServerUrl');

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
     * @param EventInterface $e
     * @return void
     */
    private function injectServiceLinks(Entity $halEntity, HalJsonModel $model, EventInterface $e)
    {
        $entity = $halEntity->getEntity();
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
     * @return void
     */
    private function injectModuleResourceRelationalLinks(Model\ModuleEntity $module, $links, HalJsonModel $model)
    {
        $moduleData = $module->getArrayCopy();
        $moduleName = $moduleData['name'];

        $this->injectLinksByType('authorization', $links, $moduleName);

        $this->injectLinksByType('rest', $links, $moduleName);
        unset($moduleData['rest']);

        $this->injectLinksByType('rpc', $links, $moduleName);
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
     * @return void
     */
    private function normalizeEntityControllerServiceName($entity, $links, HalJsonModel $model)
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
     * @return void
     */
    private function normalizeEntityInputFilterName(Model\InputFilterEntity $entity, $links, HalJsonModel $model)
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
     * @param \ArrayObject $entity
     * @param EventInterface $e
     * @return void
     */
    private function normalizeInputFilterEntityName($entity, EventInterface $e)
    {
        $entity['input_filter_name'] = str_replace('\\', '-', $entity['input_filter_name']);
        $e->setParam('entity', $entity);
    }

    /**
     * Inject service links
     *
     * @param string $type "rpc" | "rest" | "authorization"
     * @param LinkCollection $links
     * @param null|string $module
     * @return void
     */
    private function injectLinksByType($type, LinkCollection $links, $module = null)
    {
        $urlHelper = $this->urlHelper;

        $linkType = $type;
        if (in_array($type, ['rpc', 'rest'])) {
            $linkType .= '-service';
        }
        $routeName = sprintf('zf-apigility/api/module/%s', $linkType);

        $routeParams = [];
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
     * @param string $service
     * @return string
     */
    private function getServiceType($service)
    {
        if (preg_match('#[\\-.]Rest[\\-.]#', $service)) {
            return 'rest';
        }
        return 'rpc';
    }
}
