<?php
return array(
    'asset_manager' => array(
        'resolver_configs' => array(
            'paths' => array(
                __DIR__ . '/../asset',
            ),
        ),
    ),

    'view_manager' => array(
        'template_map' => array(
        'zf/app/app' => __DIR__ . '/../view/app.phtml',
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'ZF\ApiFirstAdmin\Controller\App'    => 'ZF\ApiFirstAdmin\Controller\AppController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'zf-api-first-admin' => array(
                'type'  => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'ZF\ApiFirstAdmin\Controller\App',
                        'action'     => 'app',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
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
                                        'controller' => 'ZF\ApiFirstAdmin\Controller\Source',
                                        'action'     => 'source'
                                    )
                                )
                            ),
                            'module-enable' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/module.enable',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApiFirstAdmin\Controller\ModuleCreation',
                                        'action'     => 'apiEnable',
                                    ),
                                ),
                            ),
                            'module' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/module[/:name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApiFirstAdmin\Controller\Module',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'rpc-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\ApiFirstAdmin\Controller\RpcService',
                                            ),
                                        ),
                                    ),
                                    'rest-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\ApiFirstAdmin\Controller\RestService',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                            'db-adapter' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApiFirstAdmin\Controller\DbAdapter',
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
            'ZF\ApiFirstAdmin\Controller\DbAdapter'      => 'HalJson',
            'ZF\ApiFirstAdmin\Controller\ModuleCreation' => 'HalJson',
            'ZF\ApiFirstAdmin\Controller\Source'         => 'HalJson',
            'ZF\ApiFirstAdmin\Controller\Module'         => 'HalJson',
            'ZF\ApiFirstAdmin\Controller\RestService'    => 'HalJson',
            'ZF\ApiFirstAdmin\Controller\RpcService'     => 'HalJson',
        ),
        'accept-whitelist' => array(
            'ZF\ApiFirstAdmin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\ModuleCreation' => array(
                'application/json',
            ),
            'ZF\ApiFirstAdmin\Controller\Source' => array(
                'application/json',
            ),
            'ZF\ApiFirstAdmin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
        'content-type-whitelist' => array(
            'ZF\ApiFirstAdmin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\ModuleCreation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\Source' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApiFirstAdmin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
    ),

    'zf-hal' => array(
        'metadata_map' => array(
            'ZF\ApiFirstAdmin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rest-service',
            ),
            'ZF\ApiFirstAdmin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'adapter_name',
                'route_name'      => 'zf-api-first-admin/api/db-adapter',
            ),
            'ZF\ApiFirstAdmin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'zf-api-first-admin/api/module',
            ),
            'ZF\ApiFirstAdmin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rest-service',
            ),
            'ZF\ApiFirstAdmin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rpc-service',
            ),
        ),
    ),

    'zf-rest' => array(
        'ZF\ApiFirstAdmin\Controller\DbAdapter' => array(
            'listener'                => 'ZF\ApiFirstAdmin\Model\DbAdapterResource',
            'route_name'              => 'zf-api-first-admin/api/db-adapter',
            'identifier_name'         => 'adapter_name',
            'entity_class'            => 'ZF\ApiFirstAdmin\Model\DbAdapterEntity',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'ZF\ApiFirstAdmin\Controller\Module' => array(
            'listener'                => 'ZF\ApiFirstAdmin\Model\ModuleResource',
            'route_name'              => 'zf-api-first-admin/api/module',
            'identifier_name'         => 'name',
            'entity_class'            => 'ZF\ApiFirstAdmin\Model\ModuleEntity',
            'resource_http_methods'   => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'ZF\ApiFirstAdmin\Controller\RpcService' => array(
            'listener'                => 'ZF\ApiFirstAdmin\Model\RpcServiceResource',
            'route_name'              => 'zf-api-first-admin/api/module/rpc-service',
            'entity_class'            => 'ZF\ApiFirstAdmin\Model\RpcServiceEntity',
            'identifier_name'         => 'controller_service_name',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'rpc',
        ),
        'ZF\ApiFirstAdmin\Controller\RestService' => array(
            'listener'                => 'ZF\ApiFirstAdmin\Model\RestServiceResource',
            'route_name'              => 'zf-api-first-admin/api/module/rest-service',
            'entity_class'            => 'ZF\ApiFirstAdmin\Model\RestServiceEntity',
            'identifier_name'         => 'controller_service_name',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'rest',
        ),
    ),

    'zf-rpc' => array(
        // Dummy entry; still handled by ControllerManager, but this will force
        // it to show up in the list of RPC services
        'ZF\ApiFirstAdmin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'zf-api-first-admin/api/module-enable',
        ),
        'ZF\ApiFirstAdmin\Controller\Source' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-api-first-admin/api/source',
        ),
        'ZF\Configuration\ConfigController'       => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'zf-api-first-admin/api/config',
        ),
        'ZF\Configuration\ModuleConfigController' => array(
            'http_methods' => array('GET', 'PATCH'),
            'route_name'   => 'zf-api-first-admin/api/config/module',
        ),
    ),
);
