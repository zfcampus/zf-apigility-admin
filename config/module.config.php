<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../asset/dist',
            ),
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
            'zf/app/app' => __DIR__ . '/../view/app.phtml',
        )
    ),

    'service_manager' => array(
        'factories' => array(
            'ZF\Apigility\Admin\Model\DocumentationModel' => 'ZF\Apigility\Admin\Model\DocumentationModelFactory',
            'ZF\Apigility\Admin\Model\FiltersModel' => 'ZF\Apigility\Admin\Model\FiltersModelFactory',
            'ZF\Apigility\Admin\Model\HydratorsModel' => 'ZF\Apigility\Admin\Model\HydratorsModelFactory',
            'ZF\Apigility\Admin\Model\ValidatorMetadataModel' => 'ZF\Apigility\Admin\Model\ValidatorMetadataModelFactory',
            'ZF\Apigility\Admin\Model\ValidatorsModel' => 'ZF\Apigility\Admin\Model\ValidatorsModelFactory',
            'ZF\Apigility\Admin\Model\InputFilterModel' => 'ZF\Apigility\Admin\Model\InputFilterModelFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'ZF\Apigility\Admin\Controller\App' => 'ZF\Apigility\Admin\Controller\AppController',
            'ZF\Apigility\Admin\Controller\CacheEnabled' => 'ZF\Apigility\Admin\Controller\CacheEnabledController',
        ),
        'factories' => array(
            'ZF\Apigility\Admin\Controller\Documentation' => 'ZF\Apigility\Admin\Controller\DocumentationControllerFactory',
            'ZF\Apigility\Admin\Controller\Filters' => 'ZF\Apigility\Admin\Controller\FiltersControllerFactory',
            'ZF\Apigility\Admin\Controller\Hydrators' => 'ZF\Apigility\Admin\Controller\HydratorsControllerFactory',
            'ZF\Apigility\Admin\Controller\Validators' => 'ZF\Apigility\Admin\Controller\ValidatorsControllerFactory',
            'ZF\Apigility\Admin\Controller\InputFilter' => 'ZF\Apigility\Admin\Controller\InputFilterControllerFactory',
        ),
    ),

    'router' => array(
        'routes' => array(
            'zf-apigility-admin' => array(
                'type'  => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/apigility',
                ),
                'may_terminate' => false,
                'child_routes' => array(
                    'ui' => array(
                        'type'  => 'Zend\Mvc\Router\Http\Literal',
                        'options' => array(
                            'route' => '/ui',
                            'defaults' => array(
                                'controller' => 'ZF\Apigility\Admin\Controller\App',
                                'action'     => 'app',
                            ),
                        ),
                    ),
                    'api' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/api',
                            'defaults' => array(
                                'action' => false,
                            ),
                        ),
                        'may_terminate' => false,
                        'child_routes' => array(
                            'cache-enabled' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/cache-enabled',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\CacheEnabled',
                                        'action'     => 'cacheEnabled',
                                    ),
                                ),
                            ),
                            'config' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/config',
                                    'defaults' => array(
                                        'controller' => 'ZF\Configuration\ConfigController',
                                        'action'     => 'process',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'module' => array(
                                        'type' => 'literal',
                                        'options' => array(
                                            'route' => '/module',
                                            'defaults' => array(
                                                'controller' => 'ZF\Configuration\ModuleConfigController',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'source' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/source',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Source',
                                        'action'     => 'source',
                                    ),
                                ),
                            ),
                            'filters' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/filters',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Filters',
                                        'action'     => 'filters',
                                    ),
                                ),
                            ),
                            'hydrators' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/hydrators',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Hydrators',
                                        'action'     => 'hydrators',
                                    ),
                                ),
                            ),
                            'validators' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/validators',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Validators',
                                        'action'     => 'validators',
                                    ),
                                ),
                            ),
                            'module-enable' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/module.enable',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\ModuleCreation',
                                        'action'     => 'apiEnable',
                                    ),
                                ),
                            ),
                            'versioning' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/versioning',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Versioning',
                                        'action'     => 'versioning',
                                    ),
                                ),
                            ),
                            'default-version' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/default-version',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Versioning',
                                        'action'     => 'defaultVersion',
                                    ),
                                ),
                            ),
                            'module' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/module[/:name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Module',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'authorization' => array(
                                        'type' => 'literal',
                                        'options' => array(
                                            'route' => '/authorization',
                                            'defaults' => array(
                                                'controller' => 'ZF\Apigility\Admin\Controller\Authorization',
                                                'action'     => 'authorization',
                                            ),
                                        ),
                                    ),
                                    'rpc-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\Apigility\Admin\Controller\RpcService',
                                                'controller_type' => 'rpc'
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'input-filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            ),
                                            'doc' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/doc', // [/:http_method[/:http_direction]]
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                    'rest-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\Apigility\Admin\Controller\RestService',
                                                'controller_type' => 'rest'
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'input-filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            ),
                                            'doc' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/doc', // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    )
                                                )
                                            )
                                        )
                                    ),
                                ),
                            ),
                            'authentication' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/authentication',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\Authentication',
                                        'action'     => 'authentication',
                                    ),
                                ),
                            ),
                            'db-adapter' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\DbAdapter',
                                    ),
                                ),
                            ),
                            'content-negotiation' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/content-negotiation[/:content_name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\Apigility\Admin\Controller\ContentNegotiation',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'zf-content-negotiation' => array(
        'controllers' => array(
            'ZF\Apigility\Admin\Controller\Authentication'     => 'HalJson',
            'ZF\Apigility\Admin\Controller\Authorization'      => 'HalJson',
            'ZF\Apigility\Admin\Controller\CacheEnabled'       => 'Json',
            'ZF\Apigility\Admin\Controller\ContentNegotiation' => 'HalJson',
            'ZF\Apigility\Admin\Controller\DbAdapter'          => 'HalJson',
            'ZF\Apigility\Admin\Controller\Documentation'      => 'HalJson',
            'ZF\Apigility\Admin\Controller\Filters'            => 'Json',
            'ZF\Apigility\Admin\Controller\Hydrators'          => 'Json',
            'ZF\Apigility\Admin\Controller\InputFilter'        => 'HalJson',
            'ZF\Apigility\Admin\Controller\ModuleCreation'     => 'HalJson',
            'ZF\Apigility\Admin\Controller\Module'             => 'HalJson',
            'ZF\Apigility\Admin\Controller\RestService'        => 'HalJson',
            'ZF\Apigility\Admin\Controller\RpcService'         => 'HalJson',
            'ZF\Apigility\Admin\Controller\Source'             => 'Json',
            'ZF\Apigility\Admin\Controller\Validators'         => 'Json',
            'ZF\Apigility\Admin\Controller\Versioning'         => 'Json',
        ),
        'accept_whitelist' => array(
            'ZF\Apigility\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\CacheEnabled' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\ContentNegotiation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Documentation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Filters' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Hydrators' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\InputFilter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\ModuleCreation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Source' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Validators' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Versioning' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
        'content_type_whitelist' => array(
            'ZF\Apigility\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\CacheEnabled' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\ContentNegotiation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Filters' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\Hydrators' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\InputFilter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\ModuleCreation' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\Source' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\Validators' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\Versioning' => array(
                'application/json',
            ),
            'ZF\Apigility\Admin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
    ),

    'zf-hal' => array(
        'metadata_map' => array(
            'ZF\Apigility\Admin\Model\AuthenticationEntity' => array(
                'hydrator'        => 'ArraySerializable',
            ),
            'ZF\Apigility\Admin\Model\AuthorizationEntity' => array(
                'hydrator'        => 'ArraySerializable',
            ),
            'ZF\Apigility\Admin\Model\ContentNegotiationEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'      => 'zf-apigility-admin/api/content-negotiation'
            ),
            'ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service',
            ),
            'ZF\Apigility\Admin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility-admin/api/db-adapter',
            ),
            'ZF\Apigility\Admin\Model\InputFilterCollection' => array(
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'ZF\Apigility\Admin\Model\InputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/input-filter',
            ),
            'ZF\Apigility\Admin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'name',
                'route_name'      => 'zf-apigility-admin/api/module',
            ),
            'ZF\Apigility\Admin\Model\RestInputFilterCollection' => array(
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'ZF\Apigility\Admin\Model\RestInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/input-filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'ZF\Apigility\Admin\Model\DocumentationEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/rest-doc',
            ),
            'ZF\Apigility\Admin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'zf-apigility-admin/api/module/rest-service/input-filter'
                        ),
                    ),
                    array(
                        'rel' => 'documentation',
                        'route' => array(
                            'name' => 'zf-apigility-admin/api/module/rest-service/doc',
                        ),
                    )
                ),
            ),
            'ZF\Apigility\Admin\Model\RpcInputFilterCollection' => array(
                'route_name'      => 'zf-apigility-admin/api/module/rpc-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'ZF\Apigility\Admin\Model\RpcInputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility-admin/api/module/rpc-service/input-filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ),
            'ZF\Apigility\Admin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rpc-service',
                'links'           => array(
                    array(
                        'rel' => 'input_filter',
                        'route' => array(
                            'name' => 'zf-apigility-admin/api/module/rpc-service/input-filter'
                        ),
                    ),
                    array(
                        'rel' => 'documentation',
                        'route' => array(
                            'name' => 'zf-apigility-admin/api/module/rpc-service/doc',
                        ),
                    )
                ),
            ),
        ),
    ),

    'zf-rest' => array(
        'ZF\Apigility\Admin\Controller\ContentNegotiation' => array(
            'listener'                => 'ZF\Apigility\Admin\Model\ContentNegotiationResource',
            'route_name'              => 'zf-apigility-admin/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\ContentNegotiationEntity',
            'entity_http_methods'     => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'selectors',
        ),
        'ZF\Apigility\Admin\Controller\DbAdapter' => array(
            'listener'                => 'ZF\Apigility\Admin\Model\DbAdapterResource',
            'route_name'              => 'zf-apigility-admin/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\DbAdapterEntity',
            'entity_http_methods'     => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'ZF\Apigility\Admin\Controller\Module' => array(
            'listener'                => 'ZF\Apigility\Admin\Model\ModuleResource',
            'route_name'              => 'zf-apigility-admin/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\ModuleEntity',
            'entity_http_methods'     => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'ZF\Apigility\Admin\Controller\RpcService' => array(
            'listener'                   => 'ZF\Apigility\Admin\Model\RpcServiceResource',
            'route_name'                 => 'zf-apigility-admin/api/module/rpc-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RpcServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => array('version'),
        ),
        'ZF\Apigility\Admin\Controller\RestService' => array(
            'listener'                   => 'ZF\Apigility\Admin\Model\RestServiceResource',
            'route_name'                 => 'zf-apigility-admin/api/module/rest-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RestServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rest',
            'collection_query_whitelist' => array('version'),
        ),
    ),

    'zf-rpc' => array(
        // Dummy entry; still handled by ControllerManager, but this will force
        // it to show up in the list of RPC services
        'ZF\Apigility\Admin\Controller\Authentication' => array(
            'http_methods' => array('GET', 'POST', 'PATCH', 'DELETE'),
            'route_name'   => 'zf-apigility-admin/api/authentication',
        ),
        'ZF\Apigility\Admin\Controller\Authorization' => array(
            'http_methods' => array('GET', 'PUT'),
            'route_name'   => 'zf-apigility-admin/api/module/authorization',
        ),
        'ZF\Apigility\Admin\Controller\CacheEnabled' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/cache-enabled',
        ),
        'ZF\Apigility\Admin\Controller\Documentation' => array(
            'http_methods' => array('GET', 'PATCH', 'PUT', 'DELETE'),
            'route_name'   => 'zf-apigility-admin/api/rest-service/rest-doc',
        ),
        'ZF\Apigility\Admin\Controller\Filters' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/filters',
        ),
        'ZF\Apigility\Admin\Controller\Hydrators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/hydrators',
        ),
        'ZF\Apigility\Admin\Controller\InputFilter' => array(
            'http_methods' => array('GET', 'POST', 'PUT', 'DELETE'),
            'route_name'   => 'zf-apigility-admin/api/rpc-service/input-filter',
        ),
        'ZF\Apigility\Admin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'zf-apigility-admin/api/module-enable',
        ),
        'ZF\Apigility\Admin\Controller\Source' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/source',
        ),
        'ZF\Apigility\Admin\Controller\Validators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/validators',
        ),
        'ZF\Apigility\Admin\Controller\Versioning' => array(
            'http_methods' => array('PATCH'),
            'route_name'   => 'zf-apigility-admin/api/versioning',
        ),
        'ZF\Configuration\ConfigController'       => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'zf-apigility-admin/api/config',
        ),
        'ZF\Configuration\ModuleConfigController' => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'zf-apigility-admin/api/config/module',
        ),
    ),

    /*
     * Metadata for scalar filter options.
     *
     * Each key in the map is a filter plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     */
    'filter_metadata' => array(
        'Zend\I18n\Filter\Alnum' => array(
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ),
        'Zend\I18n\Filter\Alpha' => array(
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ),
        'Zend\Filter\BaseName' => array(),
        'Zend\Filter\Boolean' => array(
            'casting' => 'bool',
            'type' => 'string',
        ),
        'Zend\Filter\Callback' => array(
            'callback' => 'string',
        ),
        'Zend\Filter\Compress\Bz2' => array(
            'archive' => 'string',
            'blocksize' => 'int',
        ),
        'Zend\Filter\Compress\Gz' => array(
            'archive' => 'string',
            'level' => 'int',
            'mode' => 'string',
        ),
        'Zend\Filter\Compress\Lzf' => array(),
        'Zend\Filter\Compress' => array(
            'adapter' => 'string',
        ),
        'Zend\Filter\Compress\Rar' => array(
            'archive' => 'string',
            'callback' => 'string',
            'password' => 'string',
            'target' => 'string',
        ),
        'Zend\Filter\Compress\Snappy' => array(),
        'Zend\Filter\Compress\Tar' => array(
            'archive' => 'string',
            'target' => 'string',
            'mode' => 'string',
        ),
        'Zend\Filter\Compress\Zip' => array(
            'archive' => 'string',
            'target' => 'string',
        ),
        'Zend\Filter\DateTimeForatter' => array(
            'format' => 'string',
        ),
        'Zend\Filter\Decompress' => array(
            'adapter' => 'string',
        ),
        'Zend\Filter\Decrypt' => array(
            'adapter' => 'string',
        ),
        'Zend\Filter\Digits' => array(),
        'Zend\Filter\Dir' => array(),
        'Zend\Filter\Encrypt\BlockCipher' => array(
            'algorithm' => 'string',
            'compression' => 'string',
            'hash' => 'string',
            'key' => 'string',
            'key_iteration' => 'int',
            'vector' => 'string',
        ),
        'Zend\Filter\Encrypt\Openssl' => array(
            'compression' => 'string',
            'package' => 'bool',
            'passphrase' => 'string',
        ),
        'Zend\Filter\Encrypt' => array(
            'adapter' => 'string',
        ),
        'Zend\Filter\File\Decrypt' => array(
            'adapter' => 'string',
            'filename' => 'string',
        ),
        'Zend\Filter\File\Encrypt' => array(
            'adapter' => 'string',
            'filename' => 'string',
        ),
        'Zend\Filter\File\LowerCase' => array(
            'encoding' => 'string',
        ),
        'Zend\Filter\File\Rename' => array(
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'source' => 'string',
            'target' => 'string',
        ),
        'Zend\Filter\File\RenameUpload' => array(
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'target' => 'string',
            'use_upload_extension' => 'bool',
            'use_upload_name' => 'bool',
        ),
        'Zend\Filter\File\Uppercase' => array(
            'encoding' => 'string',
        ),
        'Zend\Filter\HtmlEntities' => array(
            'charset' => 'string',
            'doublequote' => 'bool',
            'encoding' => 'string',
            'quotestyle' => 'int',
        ),
        'Zend\Filter\Inflector' => array(
            'throwTargetExceptionsOn' => 'bool',
            'targetReplacementIdentifier' => 'string',
            'target' => 'string',
        ),
        'Zend\Filter\Int' => array(),
        'Zend\Filter\Null' => array(
            'type' => 'int|string',
        ),
        'Zend\I18n\Filter\NumberFormat' => array(
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ),
        'Zend\I18n\Filter\NumberParse' => array(
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ),
        'Zend\Filter\PregReplace' => array(
            'pattern' => 'string',
            'replacement' => 'string',
        ),
        'Zend\Filter\RealPath' => array(
            'exists' => 'bool',
        ),
        'Zend\Filter\StringToLower' => array(
            'encoding' => 'string',
        ),
        'Zend\Filter\StringToUpper' => array(
            'encoding' => 'string',
        ),
        'Zend\Filter\StringTrim' => array(
            'charlist' => 'string',
        ),
        'Zend\Filter\StripNewlines' => array(),
        'Zend\Filter\StripTags' => array(
            'allowAttribs' => 'string',
            'allowTags' => 'string',
        ),
        'Zend\Filter\UriNormalize' => array(
            'defaultscheme' => 'string',
            'enforcedscheme' => 'string',
        ),
        'Zend\Filter\Word\CamelCaseToDash' => array(),
        'Zend\Filter\Word\CamelCaseToSeparator' => array(
            'separator' => 'string',
        ),
        'Zend\Filter\Word\CamelCaseToUnderscore' => array(),
        'Zend\Filter\Word\DashToCamelCase' => array(),
        'Zend\Filter\Word\DashToSeparator' => array(
            'separator' => 'string',
        ),
        'Zend\Filter\Word\DashToUnderscore' => array(),
        'Zend\Filter\Word\SeparatorToCamelCase' => array(
            'separator' => 'string',
        ),
        'Zend\Filter\Word\SeparatorToDash' => array(
            'separator' => 'string',
        ),
        'Zend\Filter\Word\SeparatorToSeparator' => array(
            'searchseparator' => 'string',
            'replacementseparator' => 'string',
        ),
        'Zend\Filter\Word\UnderscoreToCamelCase' => array(),
        'Zend\Filter\Word\UnderscoreToDash' => array(),
        'Zend\Filter\Word\UnderscoreToSeparator' => array(
            'separator' => 'string',
        ),
    ),

    /*
     * Metadata for scalar validator options.
     *
     * Each key in the map is a validator plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     *
     * The "__all__" key is a set of options that are true/available for all
     * validators.
     */
    'validator_metadata' => array(
        '__all__' => array(
            'breakchainonfailure' => 'bool',
            'break_chain_on_failure' => 'bool',
            'message' => 'string',
            'messagelength' => 'int',
            'valueobscured' => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled' => 'bool',
        ),
        'Zend\Validator\Barcode\Codabar' => array(),
        'Zend\Validator\Barcode\Code128' => array(),
        'Zend\Validator\Barcode\Code25interleaved' => array(),
        'Zend\Validator\Barcode\Code25' => array(),
        'Zend\Validator\Barcode\Code39ext' => array(),
        'Zend\Validator\Barcode\Code39' => array(),
        'Zend\Validator\Barcode\Code93ext' => array(),
        'Zend\Validator\Barcode\Code93' => array(),
        'Zend\Validator\Barcode\Ean12' => array(),
        'Zend\Validator\Barcode\Ean13' => array(),
        'Zend\Validator\Barcode\Ean14' => array(),
        'Zend\Validator\Barcode\Ean18' => array(),
        'Zend\Validator\Barcode\Ean2' => array(),
        'Zend\Validator\Barcode\Ean5' => array(),
        'Zend\Validator\Barcode\Ean8' => array(),
        'Zend\Validator\Barcode\Gtin12' => array(),
        'Zend\Validator\Barcode\Gtin13' => array(),
        'Zend\Validator\Barcode\Gtin14' => array(),
        'Zend\Validator\Barcode\Identcode' => array(),
        'Zend\Validator\Barcode\Intelligentmail' => array(),
        'Zend\Validator\Barcode\Issn' => array(),
        'Zend\Validator\Barcode\Itf14' => array(),
        'Zend\Validator\Barcode\Leitcode' => array(),
        'Zend\Validator\Barcode' => array(
            'adapter' => 'string', // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ),
        'Zend\Validator\Barcode\Planet' => array(),
        'Zend\Validator\Barcode\Postnet' => array(),
        'Zend\Validator\Barcode\Royalmail' => array(),
        'Zend\Validator\Barcode\Sscc' => array(),
        'Zend\Validator\Barcode\Upca' => array(),
        'Zend\Validator\Barcode\Upce' => array(),
        'Zend\Validator\Between' => array(
            'inclusive' => 'bool',
            'max' => 'int',
            'min' => 'int',
        ),
        'Zend\Validator\Bitwise' => array(
            'control' => 'int',
            'operator' => 'string',
            'strict' => 'bool',
        ),
        'Zend\Validator\Callback' => array(
            'callback' => 'string',
        ),
        'Zend\Validator\CreditCard' => array(
            'type' => 'string',
            'service' => 'string',
        ),
        'Zend\Validator\Csrf' => array(
            'name' => 'string',
            'salt' => 'string',
            'timeout' => 'int',
        ),
        'Zend\Validator\Date' => array(
            'format' => 'string',
        ),
        'Zend\Validator\DateStep' => array(
            'format' => 'string',
            'basevalue' => 'string|int',
        ),
        'Zend\Validator\Db\NoRecordExists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Zend\Validator\Db\RecordExists' => array(
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ),
        'Zend\Validator\Digits' => array(),
        'Zend\Validator\EmailAddress' => array(
            'allow' => 'int',
            'useMxCheck' => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ),
        'Zend\Validator\Explode' => array(
            'valuedelimiter' => 'string',
            'breakonfirstfailure' => 'bool',
        ),
        'Zend\Validator\File\Count' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'Zend\Validator\File\Crc32' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'crc32' => 'string',
        ),
        'Zend\Validator\File\ExcludeExtension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'Zend\Validator\File\ExcludeMimeType' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Zend\Validator\File\Exists' => array(
            'directory' => 'string',
        ),
        'Zend\Validator\File\Extension' => array(
            'case' => 'bool',
            'extension' => 'string',
        ),
        'Zend\Validator\File\FilesSize' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'Zend\Validator\File\Hash' => array(
            'algorithm' => 'string',
            'hash' => 'string',
        ),
        'Zend\Validator\File\ImageSize' => array(
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth' => 'int',
            'minWidth' => 'int',
        ),
        'Zend\Validator\File\IsCompressed' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Zend\Validator\File\IsImage' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Zend\Validator\File\Md5' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'md5' => 'string',
        ),
        'Zend\Validator\File\MimeType' => array(
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ),
        'Zend\Validator\File\NotExists' => array(
            'directory' => 'string',
        ),
        'Zend\Validator\File\Sha1' => array(
            'algorithm' => 'string',
            'hash' => 'string',
            'sha1' => 'string',
        ),
        'Zend\Validator\File\Size' => array(
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ),
        'Zend\Validator\File\UploadFile' => array(),
        'Zend\Validator\File\Upload' => array(),
        'Zend\Validator\File\WordCount' => array(
            'max' => 'int',
            'min' => 'int',
        ),
        'Zend\Validator\GreaterThan' => array(
            'inclusive' => 'bool',
            'min' => 'int',
        ),
        'Zend\Validator\Hex' => array(),
        'Zend\Validator\Hostname' => array(
            'allow' => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ),
        'Zend\Validator\Iban' => array(
            'country_code' => 'string',
            'allow_non_sepa' => 'bool',
        ),
        'Zend\Validator\Identical' => array(
            'literal' => 'bool',
            'strict' => 'bool',
            'token' => 'string',
        ),
        'Zend\Validator\InArray' => array(
            'strict' => 'bool',
            'recursive' => 'bool',
        ),
        'Zend\Validator\Ip' => array(
            'allowipv4' => 'bool',
            'allowipv6' => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral' => 'bool',
        ),
        'Zend\Validator\Isbn' => array(
            'type' => 'string',
            'separator' => 'string',
        ),
        'Zend\Validator\IsInstanceOf' => array(
            'classname' => 'string',
        ),
        'Zend\Validator\LessThan' => array(
            'inclusive' => 'bool',
            'max' => 'int',
        ),
        'Zend\Validator\NotEmpty' => array(
            'type' => 'int',
        ),
        'Zend\Validator\Regex' => array(
            'pattern' => 'string',
        ),
        'Zend\Validator\Sitemap\Changefreq' => array(),
        'Zend\Validator\Sitemap\Lastmod' => array(),
        'Zend\Validator\Sitemap\Loc' => array(),
        'Zend\Validator\Sitemap\Priority' => array(),
        'Zend\Validator\Step' => array(
            'baseValue' => 'int|float',
            'step' => 'float',
        ),
        'Zend\Validator\StringLength' => array(
            'max' => 'int',
            'min' => 'int',
            'encoding' => 'string',
        ),
        'Zend\Validator\Uri' => array(
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ),
        'Zend\I18n\Validator\Alnum' => array(
            'allowwhitespace' => 'bool',
        ),
        'Zend\I18n\Validator\Alpha' => array(
            'allowwhitespace' => 'bool',
        ),
        'Zend\I18n\Validator\DateTime' => array(
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern' => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale' => 'string',
        ),
        'Zend\I18n\Validator\Float' => array(
            'locale' => 'string',
        ),
        'Zend\I18n\Validator\Int' => array(
            'locale' => 'string',
        ),
        'Zend\I18n\Validator\PhoneNumber' => array(
            'country' => 'string',
            'allow_possible' => 'bool',
        ),
        'Zend\I18n\Validator\PostCode' => array(
            'locale' => 'string',
            'format' => 'string',
            'service' => 'string',
        ),
    ),

    'input_filters' => array(
        'ZF\Apigility\Admin\ModuleName\Validator' => array(
            array(
                'name' => 'name',
                'validators' => array(
                    array(
                        'name' => 'regex',
                        'options' => array(
                            'pattern' => '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/',
                            'message' => 'Invalid API name; must be a valid PHP namespace name',
                        ),
                    ),
                ),
            ),
        ),
    ),

    'zf-content-validation' => array(
        'ZF\Apigility\Admin\Controller\Module' => array(
            'input_filter' => 'ZF\Apigility\Admin\ModuleName\Validator',
        ),
    ),
);
