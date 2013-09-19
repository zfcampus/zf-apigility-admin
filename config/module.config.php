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
            'ZF\ApigilityAdmin\Controller\App' => 'ZF\ApigilityAdmin\Controller\AppController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'zf-api-first-admin' => array(
                'type'  => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'ZF\ApigilityAdmin\Controller\App',
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
                            'module-enable' => array(
                                'type' => 'literal',
                                'options' => array(
                                    'route' => '/module.enable',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApigilityAdmin\Controller\ModuleCreation',
                                        'action'     => 'apiEnable',
                                    ),
                                ),
                            ),
                            'module' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/module[/:name]',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApigilityAdmin\Controller\Module',
                                    ),
                                ),
                                'may_terminate' => true,
                                'child_routes' => array(
                                    'rpc-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\ApigilityAdmin\Controller\RpcService',
                                            ),
                                        ),
                                    ),
                                    'rest-service' => array(
                                        'type' => 'segment',
                                        'options' => array(
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => array(
                                                'controller' => 'ZF\ApigilityAdmin\Controller\RestService',
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
                                        'controller' => 'ZF\ApigilityAdmin\Controller\DbAdapter',
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
            'ZF\ApigilityAdmin\Controller\DbAdapter'      => 'HalJson',
            'ZF\ApigilityAdmin\Controller\ModuleCreation' => 'HalJson',
            'ZF\ApigilityAdmin\Controller\Module'         => 'HalJson',
            'ZF\ApigilityAdmin\Controller\RestService'    => 'HalJson',
            'ZF\ApigilityAdmin\Controller\RpcService'     => 'HalJson',
        ),
        'accept-whitelist' => array(
            'ZF\ApigilityAdmin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\ModuleCreation' => array(
                'application/json',
            ),
            'ZF\ApigilityAdmin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
        'content-type-whitelist' => array(
            'ZF\ApigilityAdmin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\Module' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\ModuleCreation' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\RestService' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\ApigilityAdmin\Controller\RpcService' => array(
                'application/json',
                'application/*+json',
            ),
        ),
    ),

    'zf-hal' => array(
        'metadata_map' => array(
            'ZF\ApigilityAdmin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rest-service',
            ),
            'ZF\ApigilityAdmin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'adapter_name',
                'route_name'      => 'zf-api-first-admin/api/db-adapter',
            ),
            'ZF\ApigilityAdmin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'zf-api-first-admin/api/module',
            ),
            'ZF\ApigilityAdmin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rest-service',
            ),
            'ZF\ApigilityAdmin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-api-first-admin/api/module/rpc-service',
            ),
        ),
    ),

    'zf-rest' => array(
        'ZF\ApigilityAdmin\Controller\DbAdapter' => array(
            'listener'                => 'ZF\ApigilityAdmin\Model\DbAdapterResource',
            'route_name'              => 'zf-api-first-admin/api/db-adapter',
            'identifier_name'         => 'adapter_name',
            'entity_class'            => 'ZF\ApigilityAdmin\Model\DbAdapterEntity',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'ZF\ApigilityAdmin\Controller\Module' => array(
            'listener'                => 'ZF\ApigilityAdmin\Model\ModuleResource',
            'route_name'              => 'zf-api-first-admin/api/module',
            'identifier_name'         => 'name',
            'entity_class'            => 'ZF\ApigilityAdmin\Model\ModuleEntity',
            'resource_http_methods'   => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'ZF\ApigilityAdmin\Controller\RpcService' => array(
            'listener'                => 'ZF\ApigilityAdmin\Model\RpcServiceResource',
            'route_name'              => 'zf-api-first-admin/api/module/rpc-service',
            'entity_class'            => 'ZF\ApigilityAdmin\Model\RpcServiceEntity',
            'identifier_name'         => 'controller_service_name',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'rpc',
        ),
        'ZF\ApigilityAdmin\Controller\RestService' => array(
            'listener'                => 'ZF\ApigilityAdmin\Model\RestServiceResource',
            'route_name'              => 'zf-api-first-admin/api/module/rest-service',
            'entity_class'            => 'ZF\ApigilityAdmin\Model\RestServiceEntity',
            'identifier_name'         => 'controller_service_name',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'rest',
        ),
    ),

    'zf-rpc' => array(
        // Dummy entry; still handled by ControllerManager, but this will force
        // it to show up in the list of RPC services
        'ZF\ApigilityAdmin\Controller\ModuleCreation' => array(
            'http_methods' => array('PUT'),
            'route_name'   => 'zf-api-first-admin/api/module-enable',
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
