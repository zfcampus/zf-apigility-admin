<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

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

    'service_manager' => array(
        'factories' => array(
            'ZF\Apigility\Admin\Model\HydratorsModel' => 'ZF\Apigility\Admin\Model\HydratorsModelFactory',
            'ZF\Apigility\Admin\Model\ValidatorsModel' => 'ZF\Apigility\Admin\Model\ValidatorsModelFactory',
            'ZF\Apigility\Admin\Model\InputFilterModel' => 'ZF\Apigility\Admin\Model\InputFilterModelFactory',
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'ZF\Apigility\Admin\Controller\App' => 'ZF\Apigility\Admin\Controller\AppController',
        ),
        'factories' => array(
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
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'ZF\Apigility\Admin\Controller\App',
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
                                        'controller' => 'ZF\Apigility\Admin\Controller\Source',
                                        'action'     => 'source',
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
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'rpc_input_filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/inputfilter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
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
                                            ),
                                        ),
                                        'may_terminate' => true,
                                        'child_routes' => array(
                                            'rpc_input_filter' => array(
                                                'type' => 'segment',
                                                'options' => array(
                                                    'route' => '/inputfilter[/:input_filter_name]',
                                                    'defaults' => array(
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
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
                        ),
                    ),
                ),
            ),
        ),
    ),

    'zf-content-negotiation' => array(
        'controllers' => array(
            'ZF\Apigility\Admin\Controller\Authentication' => 'HalJson',
            'ZF\Apigility\Admin\Controller\Authorization'  => 'HalJson',
            'ZF\Apigility\Admin\Controller\DbAdapter'      => 'HalJson',
            'ZF\Apigility\Admin\Controller\Hydrators'      => 'Json',
            'ZF\Apigility\Admin\Controller\InputFilter'    => 'HalJson',
            'ZF\Apigility\Admin\Controller\ModuleCreation' => 'HalJson',
            'ZF\Apigility\Admin\Controller\Module'         => 'HalJson',
            'ZF\Apigility\Admin\Controller\RestService'    => 'HalJson',
            'ZF\Apigility\Admin\Controller\RpcService'     => 'HalJson',
            'ZF\Apigility\Admin\Controller\Source'         => 'Json',
            'ZF\Apigility\Admin\Controller\Validators'     => 'Json',
            'ZF\Apigility\Admin\Controller\Versioning'     => 'Json',
        ),
        'accept-whitelist' => array(
            'ZF\Apigility\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\DbAdapter' => array(
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
        'content-type-whitelist' => array(
            'ZF\Apigility\Admin\Controller\Authentication' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\Authorization' => array(
                'application/json',
                'application/*+json',
            ),
            'ZF\Apigility\Admin\Controller\DbAdapter' => array(
                'application/json',
                'application/*+json',
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
            'ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service',
            ),
            'ZF\Apigility\Admin\Model\DbAdapterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility-admin/api/db-adapter',
            ),
            'ZF\Apigility\Admin\Model\InputFilterEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service/rest_input_filter',
            ),
            'ZF\Apigility\Admin\Model\ModuleEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'name',
                'route_name'      => 'zf-apigility-admin/api/module',
            ),
            'ZF\Apigility\Admin\Model\RestServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rest-service',
            ),
            'ZF\Apigility\Admin\Model\RpcServiceEntity' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility-admin/api/module/rpc-service',
            ),
        ),
    ),

    'zf-rest' => array(
        'ZF\Apigility\Admin\Controller\DbAdapter' => array(
            'listener'                => 'ZF\Apigility\Admin\Model\DbAdapterResource',
            'route_name'              => 'zf-apigility-admin/api/db-adapter',
            'identifier_name'         => 'adapter_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\DbAdapterEntity',
            'resource_http_methods'   => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'db_adapter',
        ),
        'ZF\Apigility\Admin\Controller\Module' => array(
            'listener'                => 'ZF\Apigility\Admin\Model\ModuleResource',
            'route_name'              => 'zf-apigility-admin/api/module',
            'identifier_name'         => 'name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\ModuleEntity',
            'resource_http_methods'   => array('GET'),
            'collection_http_methods' => array('GET', 'POST'),
            'collection_name'         => 'module',
        ),
        'ZF\Apigility\Admin\Controller\RpcService' => array(
            'listener'                   => 'ZF\Apigility\Admin\Model\RpcServiceResource',
            'route_name'                 => 'zf-apigility-admin/api/module/rpc-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RpcServiceEntity',
            'identifier_name'            => 'controller_service_name',
            'resource_http_methods'      => array('GET', 'PATCH', 'DELETE'),
            'collection_http_methods'    => array('GET', 'POST'),
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => array('version'),
        ),
        'ZF\Apigility\Admin\Controller\RestService' => array(
            'listener'                   => 'ZF\Apigility\Admin\Model\RestServiceResource',
            'route_name'                 => 'zf-apigility-admin/api/module/rest-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RestServiceEntity',
            'identifier_name'            => 'controller_service_name',
            'resource_http_methods'      => array('GET', 'PATCH', 'DELETE'),
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
        'ZF\Apigility\Admin\Controller\Hydrators' => array(
            'http_methods' => array('GET'),
            'route_name'   => 'zf-apigility-admin/api/hydrators',
        ),
        'ZF\Apigility\Admin\Controller\InputFilter' => array(
            'http_methods' => array('GET', 'PUT', 'DELETE'),
            'route_name'   => 'zf-apigility-admin/api/rpc-service/rpc_input_filter',
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
);
