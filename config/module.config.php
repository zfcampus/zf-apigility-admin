<?php // @codingStandardsIgnoreFile
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

return [
    'service_manager' => [
        'invokables' => [
            'ZF\Apigility\Admin\Listener\CryptFilterListener' => 'ZF\Apigility\Admin\Listener\CryptFilterListener',
        ],
        'factories' => [
            'ZF\Apigility\Admin\Model\DocumentationModel' => 'ZF\Apigility\Admin\Model\DocumentationModelFactory',
            'ZF\Apigility\Admin\Model\FiltersModel' => 'ZF\Apigility\Admin\Model\FiltersModelFactory',
            'ZF\Apigility\Admin\Model\HydratorsModel' => 'ZF\Apigility\Admin\Model\HydratorsModelFactory',
            'ZF\Apigility\Admin\Model\ValidatorMetadataModel' => 'ZF\Apigility\Admin\Model\ValidatorMetadataModelFactory',
            'ZF\Apigility\Admin\Model\ValidatorsModel' => 'ZF\Apigility\Admin\Model\ValidatorsModelFactory',
            'ZF\Apigility\Admin\Model\InputFilterModel' => 'ZF\Apigility\Admin\Model\InputFilterModelFactory',
        ],
    ],

    'controllers' => [
        'aliases' => [
            'ZF\Apigility\Admin\Controller\HttpBasicAuthentication' => 'ZF\Apigility\Admin\Controller\Authentication',
            'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => 'ZF\Apigility\Admin\Controller\Authentication',
            'ZF\Apigility\Admin\Controller\OAuth2Authentication' => 'ZF\Apigility\Admin\Controller\Authentication',
        ],
        'invokables' => [
            'ZF\Apigility\Admin\Controller\App' => 'ZF\Apigility\Admin\Controller\AppController',
            'ZF\Apigility\Admin\Controller\CacheEnabled' => 'ZF\Apigility\Admin\Controller\CacheEnabledController',
            'ZF\Apigility\Admin\Controller\FsPermissions' => 'ZF\Apigility\Admin\Controller\FsPermissionsController',
            'ZF\Apigility\Admin\Controller\Strategy' => 'ZF\Apigility\Admin\Controller\StrategyController',
            'ZF\Apigility\Admin\Controller\Package' => 'ZF\Apigility\Admin\Controller\PackageController'
        ],
        'factories' => [
            'ZF\Apigility\Admin\Controller\AuthenticationType' => 'ZF\Apigility\Admin\Controller\AuthenticationTypeControllerFactory',
            'ZF\Apigility\Admin\Controller\DbAutodiscovery' => 'ZF\Apigility\Admin\Controller\DbAutodiscoveryControllerFactory',
            'ZF\Apigility\Admin\Controller\Dashboard' => 'ZF\Apigility\Admin\Controller\DashboardControllerFactory',
            'ZF\Apigility\Admin\Controller\Documentation' => 'ZF\Apigility\Admin\Controller\DocumentationControllerFactory',
            'ZF\Apigility\Admin\Controller\Filters' => 'ZF\Apigility\Admin\Controller\FiltersControllerFactory',
            'ZF\Apigility\Admin\Controller\Hydrators' => 'ZF\Apigility\Admin\Controller\HydratorsControllerFactory',
            'ZF\Apigility\Admin\Controller\InputFilter' => 'ZF\Apigility\Admin\Controller\InputFilterControllerFactory',
            'ZF\Apigility\Admin\Controller\SettingsDashboard' => 'ZF\Apigility\Admin\Controller\DashboardControllerFactory',
            'ZF\Apigility\Admin\Controller\Validators' => 'ZF\Apigility\Admin\Controller\ValidatorsControllerFactory',
        ],
    ],

    'router' => [
        'routes' => [
            'zf-apigility' => [
                'child_routes' => [
                    'ui' => [
                        'type'  => 'Zend\Mvc\Router\Http\Literal',
                        'options' => [
                            'route' => '/ui',
                            'defaults' => [
                                'controller' => 'ZF\Apigility\Admin\Controller\App',
                                'action'     => 'app',
                            ],
                        ],
                    ],
                    'api' => [
                        'type' => 'literal',
                        'options' => [
                            'route' => '/api',
                            'defaults' => [
                                'is_apigility_admin_api' => true,
                                'action'                 => false,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'dashboard' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Dashboard',
                                        'action'     => 'dashboard',
                                    ],
                                ],
                            ],
                            'settings-dashboard' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/settings-dashboard',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\SettingsDashboard',
                                        'action'     => 'settingsDashboard',
                                    ],
                                ],
                            ],
                            'strategy' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/strategy/:strategy_name',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Strategy',
                                        'action'     => 'exists',
                                    ],
                                ],
                            ],
                            'cache-enabled' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/cache-enabled',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\CacheEnabled',
                                        'action'     => 'cacheEnabled',
                                    ],
                                ],
                            ],
                            'fs-permissions' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/fs-permissions',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\FsPermissions',
                                        'action'     => 'fsPermissions',
                                    ],
                                ],
                            ],
                            'config' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/config',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Config',
                                        'action'     => 'process',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'module' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/module',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\ModuleConfig',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'source' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/source',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Source',
                                        'action'     => 'source',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Filters',
                                        'action'     => 'filters',
                                    ],
                                ],
                            ],
                            'hydrators' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/hydrators',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Hydrators',
                                        'action'     => 'hydrators',
                                    ],
                                ],
                            ],
                            'validators' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/validators',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Validators',
                                        'action'     => 'validators',
                                    ],
                                ],
                            ],
                            'module-enable' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/module.enable',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\ModuleCreation',
                                        'action'     => 'apiEnable',
                                    ],
                                ],
                            ],
                            'versioning' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/versioning',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Versioning',
                                        'action'     => 'versioning',
                                    ],
                                ],
                            ],
                            'default-version' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/default-version',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Versioning',
                                        'action'     => 'defaultVersion',
                                    ],
                                ],
                            ],
                            'module' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/module[/:name]',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Module',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'authentication' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/authentication',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\Authentication',
                                                'action'     => 'mapping',
                                            ],
                                        ],
                                    ],
                                    'authorization' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/authorization',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\Authorization',
                                                'action'     => 'authorization',
                                            ],
                                        ],
                                    ],
                                    'rpc-service' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\RpcService',
                                                'controller_type' => 'rpc'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    ]
                                                ]
                                            ],
                                            'doc' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:http_method[/:http_direction]]
                                                    'defaults' => [
                                                        'controller' => 'ZF\Apigility\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'rest-service' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\RestService',
                                                'controller_type' => 'rest'
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => 'ZF\Apigility\Admin\Controller\InputFilter',
                                                        'action'     => 'index',
                                                    ]
                                                ]
                                            ],
                                            'doc' => [
                                                'type' => 'segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => [
                                                        'controller' => 'ZF\Apigility\Admin\Controller\Documentation',
                                                        'action'     => 'index',
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    'db-autodiscovery' => [
                                        'type' => 'segment',
                                        'options' => [
                                            'route' => '/:version/autodiscovery/:adapter_name',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\DbAutodiscovery',
                                                'action' => 'discover',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'authentication' => [
                                'type' => 'Zend\Mvc\Router\Http\Segment',
                                'options' => [
                                    'route' => '/authentication[/:authentication_adapter]',
                                    'defaults' => [
                                        'action'     => 'authentication',
                                        'controller' => 'ZF\Apigility\Admin\Controller\Authentication',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'oauth2' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/oauth2',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\OAuth2Authentication',
                                            ],
                                        ],
                                    ],
                                    'http-basic' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/http-basic',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\HttpBasicAuthentication',
                                            ],
                                        ],
                                    ],
                                    'http-digest' => [
                                        'type' => 'literal',
                                        'options' => [
                                            'route' => '/http-digest',
                                            'defaults' => [
                                                'controller' => 'ZF\Apigility\Admin\Controller\HttpDigestAuthentication',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'db-adapter' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\DbAdapter',
                                    ],
                                ],
                            ],
                            'doctrine-adapter' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/doctrine-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\DoctrineAdapter',
                                    ],
                                ],
                            ],
                            'content-negotiation' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/content-negotiation[/:content_name]',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\ContentNegotiation',
                                    ],
                                ],
                            ],
                            'package' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/package',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\Package',
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'authentication-type' => [
                                'type' => 'literal',
                                'options' => [
                                    'route' => '/auth-type',
                                    'defaults' => [
                                        'controller' => 'ZF\Apigility\Admin\Controller\AuthenticationType',
                                        'action'     => 'authType',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'zf-content-negotiation' => [
        'controllers' => [
            'ZF\Apigility\Admin\Controller\Authentication'           => 'HalJson',
            'ZF\Apigility\Admin\Controller\AuthenticationType'       => 'Json',
            'ZF\Apigility\Admin\Controller\Authorization'            => 'HalJson',
            'ZF\Apigility\Admin\Controller\CacheEnabled'             => 'Json',
            'ZF\Apigility\Admin\Controller\ContentNegotiation'       => 'HalJson',
            'ZF\Apigility\Admin\Controller\Dashboard'                => 'HalJson',
            'ZF\Apigility\Admin\Controller\DbAdapter'                => 'HalJson',
            'ZF\Apigility\Admin\Controller\DbAutodiscovery'          => 'Json',
            'ZF\Apigility\Admin\Controller\DoctrineAdapter'          => 'HalJson',
            'ZF\Apigility\Admin\Controller\Documentation'            => 'HalJson',
            'ZF\Apigility\Admin\Controller\Filters'                  => 'Json',
            'ZF\Apigility\Admin\Controller\FsPermissions'            => 'Json',
            'ZF\Apigility\Admin\Controller\HttpBasicAuthentication'  => 'HalJson',
            'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => 'HalJson',
            'ZF\Apigility\Admin\Controller\Hydrators'                => 'Json',
            'ZF\Apigility\Admin\Controller\InputFilter'              => 'HalJson',
            'ZF\Apigility\Admin\Controller\ModuleCreation'           => 'HalJson',
            'ZF\Apigility\Admin\Controller\Module'                   => 'HalJson',
            'ZF\Apigility\Admin\Controller\OAuth2Authentication'     => 'HalJson',
            'ZF\Apigility\Admin\Controller\RestService'              => 'HalJson',
            'ZF\Apigility\Admin\Controller\RpcService'               => 'HalJson',
            'ZF\Apigility\Admin\Controller\SettingsDashboard'        => 'HalJson',
            'ZF\Apigility\Admin\Controller\Source'                   => 'Json',
            'ZF\Apigility\Admin\Controller\Strategy'                 => 'Json',
            'ZF\Apigility\Admin\Controller\Validators'               => 'Json',
            'ZF\Apigility\Admin\Controller\Versioning'               => 'Json',
            'ZF\Apigility\Admin\Controller\Package'                  => 'Json'
        ],
        'accept_whitelist' => [
            'ZF\Apigility\Admin\Controller\Authentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Authorization' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\CacheEnabled' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\ContentNegotiation' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Dashboard' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DbAdapter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DbAutodiscovery' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DoctrineAdapter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Documentation' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Filters' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\FsPermissions' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\HttpBasicAuthentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Hydrators' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\InputFilter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Module' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\ModuleCreation' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\OAuth2Authentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\SettingsDashboard' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Source' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Strategy' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Validators' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Versioning' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\RestService' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\RpcService' => [
                'application/json',
                'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            'ZF\Apigility\Admin\Controller\Authorization' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\CacheEnabled' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\ContentNegotiation' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Dashboard' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DbAdapter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DbAutodiscovery' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\DoctrineAdapter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Filters' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\FsPermissions' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Hydrators' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\HttpBasicAuthentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\InputFilter' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Module' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\ModuleCreation' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\OAuth2Authentication' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\SettingsDashboard' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\Source' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\Strategy' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\Validators' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\Versioning' => [
                'application/json',
            ],
            'ZF\Apigility\Admin\Controller\RestService' => [
                'application/json',
                'application/*+json',
            ],
            'ZF\Apigility\Admin\Controller\RpcService' => [
                'application/json',
                'application/*+json',
            ],
        ],
    ],

    'zf-hal' => [
        'metadata_map' => [
            'ZF\Apigility\Admin\Model\AuthenticationEntity' => [
                'hydrator'        => 'ArraySerializable',
            ],
            'ZF\Apigility\Admin\Model\AuthorizationEntity' => [
                'hydrator'        => 'ArraySerializable',
            ],
            'ZF\Apigility\Admin\Model\ContentNegotiationEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'      => 'zf-apigility/api/content-negotiation'
            ],
            'ZF\Apigility\Admin\Model\DbConnectedRestServiceEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rest-service',
            ],
            'ZF\Apigility\Admin\Model\DbAdapterEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility/api/db-adapter',
            ],
            'ZF\Apigility\Admin\Model\DoctrineAdapterEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility/api/doctrine-adapter',
            ],
            'ZF\Apigility\Admin\Model\InputFilterCollection' => [
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            'ZF\Apigility\Admin\Model\InputFilterEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
            ],
            'ZF\Apigility\Admin\Model\ModuleEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'name',
                'entity_identifier_name' => 'name',
                'route_name'      => 'zf-apigility/api/module',
            ],
            'ZF\Apigility\Admin\Model\RestInputFilterCollection' => [
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            'ZF\Apigility\Admin\Model\RestInputFilterEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            'ZF\Apigility\Admin\Model\DocumentationEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'      => 'zf-apigility/api/module/rest-service/rest-doc',
            ],
            'ZF\Apigility\Admin\Model\RestServiceEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rest-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rest-service/input-filter'
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rest-service/doc',
                        ],
                    ]
                ],
            ],
            'ZF\Apigility\Admin\Model\RpcInputFilterCollection' => [
                'route_name'      => 'zf-apigility/api/module/rpc-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            'ZF\Apigility\Admin\Model\RpcInputFilterEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rpc-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            'ZF\Apigility\Admin\Model\RpcServiceEntity' => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rpc-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rpc-service/input-filter'
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rpc-service/doc',
                        ],
                    ]
                ],
            ],
        ],
    ],

    'zf-rest' => [
        'ZF\Apigility\Admin\Controller\ContentNegotiation' => [
            'listener'                => 'ZF\Apigility\Admin\Model\ContentNegotiationResource',
            'route_name'              => 'zf-apigility/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\ContentNegotiationEntity',
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'selectors',
        ],
        'ZF\Apigility\Admin\Controller\DbAdapter' => [
            'listener'                => 'ZF\Apigility\Admin\Model\DbAdapterResource',
            'route_name'              => 'zf-apigility/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\DbAdapterEntity',
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'db_adapter',
        ],
        'ZF\Apigility\Admin\Controller\DoctrineAdapter' => [
            'listener'                => 'ZF\Apigility\Admin\Model\DoctrineAdapterResource',
            'route_name'              => 'zf-apigility/api/doctrine-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\DoctrineAdapterEntity',
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET'],
            'collection_name'         => 'doctrine_adapter',
        ],
        'ZF\Apigility\Admin\Controller\Module' => [
            'listener'                => 'ZF\Apigility\Admin\Model\ModuleResource',
            'route_name'              => 'zf-apigility/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => 'ZF\Apigility\Admin\Model\ModuleEntity',
            'entity_http_methods'     => ['GET', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'module',
        ],
        'ZF\Apigility\Admin\Controller\RpcService' => [
            'listener'                   => 'ZF\Apigility\Admin\Model\RpcServiceResource',
            'route_name'                 => 'zf-apigility/api/module/rpc-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RpcServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => ['version'],
        ],
        'ZF\Apigility\Admin\Controller\RestService' => [
            'listener'                   => 'ZF\Apigility\Admin\Model\RestServiceResource',
            'route_name'                 => 'zf-apigility/api/module/rest-service',
            'entity_class'               => 'ZF\Apigility\Admin\Model\RestServiceEntity',
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rest',
            'collection_query_whitelist' => ['version'],
        ],
    ],

    'zf-rpc' => [
        'ZF\Apigility\Admin\Controller\Authentication' => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication',
        ],
        'ZF\Apigility\Admin\Controller\AuthenticationType' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/authentication-type',
        ],
        'ZF\Apigility\Admin\Controller\Authorization' => [
            'http_methods' => ['GET', 'PATCH', 'PUT'],
            'route_name'   => 'zf-apigility/api/module/authorization',
        ],
        'ZF\Apigility\Admin\Controller\CacheEnabled' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/cache-enabled',
        ],
        'ZF\Apigility\Admin\Controller\Config' => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'zf-apigility/api/config',
        ],
        'ZF\Apigility\Admin\Controller\Dashboard' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/dashboard',
        ],
        'ZF\Apigility\Admin\Controller\DbAutodiscovery' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/module/db-autodiscovery',
        ],
        'ZF\Apigility\Admin\Controller\Documentation' => [
            'http_methods' => ['GET', 'PATCH', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/rest-service/rest-doc',
        ],
        'ZF\Apigility\Admin\Controller\Filters' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/filters',
        ],
        'ZF\Apigility\Admin\Controller\FsPermissions' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/fs-permissions',
        ],
        'ZF\Apigility\Admin\Controller\HttpBasicAuthentication' => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/http-basic',
        ],
        'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/http-digest',
        ],
        'ZF\Apigility\Admin\Controller\Hydrators' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/hydrators',
        ],
        'ZF\Apigility\Admin\Controller\InputFilter' => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/rpc-service/input-filter',
        ],
        'ZF\Apigility\Admin\Controller\ModuleConfig' => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'zf-apigility/api/config/module',
        ],
        'ZF\Apigility\Admin\Controller\ModuleCreation' => [
            'http_methods' => ['PUT'],
            'route_name'   => 'zf-apigility/api/module-enable',
        ],
        'ZF\Apigility\Admin\Controller\OAuth2Authentication' => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/oauth2',
        ],
        'ZF\Apigility\Admin\Controller\SettingsDashboard' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/settings-dashboard',
        ],
        'ZF\Apigility\Admin\Controller\Source' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/source',
        ],
        'ZF\Apigility\Admin\Controller\Validators' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/validators',
        ],
        'ZF\Apigility\Admin\Controller\Versioning' => [
            'http_methods' => ['PATCH'],
            'route_name'   => 'zf-apigility/api/versioning',
        ],
        'ZF\Apigility\Admin\Controller\Strategy' => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/strategy'
        ],
        'ZF\Apigility\Admin\Controller\Package' => [
            'http_methods' => ['GET', 'POST'],
            'route_name'   => 'zf-apigility/api/package',
        ],
    ],

    /*
     * Metadata for scalar filter options.
     *
     * Each key in the map is a filter plugin name. The value is an array of
     * option key/type pairs. If more than one type is possible, the types are
     * OR'd.
     */
    'filter_metadata' => [
        'Zend\I18n\Filter\Alnum' => [
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ],
        'Zend\I18n\Filter\Alpha' => [
            'allow_white_space' => 'bool',
            'locale' => 'string',
        ],
        'Zend\Filter\BaseName' => [],
        'Zend\Filter\Boolean' => [
            'casting' => 'bool',
            'type' => 'string',
        ],
        'Zend\Filter\Callback' => [
            'callback' => 'string',
        ],
        'Zend\Filter\Compress\Bz2' => [
            'archive' => 'string',
            'blocksize' => 'int',
        ],
        'Zend\Filter\Compress\Gz' => [
            'archive' => 'string',
            'level' => 'int',
            'mode' => 'string',
        ],
        'Zend\Filter\Compress\Lzf' => [],
        'Zend\Filter\Compress' => [
            'adapter' => 'string',
        ],
        'Zend\Filter\Compress\Rar' => [
            'archive' => 'string',
            'callback' => 'string',
            'password' => 'string',
            'target' => 'string',
        ],
        'Zend\Filter\Compress\Snappy' => [],
        'Zend\Filter\Compress\Tar' => [
            'archive' => 'string',
            'target' => 'string',
            'mode' => 'string',
        ],
        'Zend\Filter\Compress\Zip' => [
            'archive' => 'string',
            'target' => 'string',
        ],
        'Zend\Filter\DateTimeFormatter' => [
            'format' => 'string',
        ],
        'Zend\Filter\Decompress' => [
            'adapter' => 'string',
        ],
        'Zend\Filter\Decrypt' => [
            'adapter' => 'string',
        ],
        'Zend\Filter\Digits' => [],
        'Zend\Filter\Dir' => [],
        'Zend\Filter\Encrypt\BlockCipher' => [
            'algorithm' => 'string',
            'compression' => 'string',
            'hash' => 'string',
            'key' => 'string',
            'key_iteration' => 'int',
            'vector' => 'string',
        ],
        'Zend\Filter\Encrypt\Openssl' => [
            'compression' => 'string',
            'package' => 'bool',
            'passphrase' => 'string',
        ],
        'Zend\Filter\Encrypt' => [
            'adapter' => 'string',
        ],
        'Zend\Filter\File\Decrypt' => [
            'adapter' => 'string',
            'filename' => 'string',
        ],
        'Zend\Filter\File\Encrypt' => [
            'adapter' => 'string',
            'filename' => 'string',
        ],
        'Zend\Filter\File\LowerCase' => [
            'encoding' => 'string',
        ],
        'Zend\Filter\File\Rename' => [
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'source' => 'string',
            'target' => 'string',
        ],
        'Zend\Filter\File\RenameUpload' => [
            'overwrite' => 'bool',
            'randomize' => 'bool',
            'target' => 'string',
            'use_upload_extension' => 'bool',
            'use_upload_name' => 'bool',
        ],
        'Zend\Filter\File\UpperCase' => [
            'encoding' => 'string',
        ],
        'Zend\Filter\HtmlEntities' => [
            'charset' => 'string',
            'doublequote' => 'bool',
            'encoding' => 'string',
            'quotestyle' => 'int',
        ],
        'Zend\Filter\Inflector' => [
            'throwTargetExceptionsOn' => 'bool',
            'targetReplacementIdentifier' => 'string',
            'target' => 'string',
        ],
        'Zend\Filter\Int' => [],
        'Zend\Filter\Null' => [
            'type' => 'int|string',
        ],
        'Zend\I18n\Filter\NumberFormat' => [
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ],
        'Zend\I18n\Filter\NumberParse' => [
            'locale' => 'string',
            'style' => 'int',
            'type' => 'int',
        ],
        'Zend\Filter\PregReplace' => [
            'pattern' => 'string',
            'replacement' => 'string',
        ],
        'Zend\Filter\RealPath' => [
            'exists' => 'bool',
        ],
        'Zend\Filter\StringToLower' => [
            'encoding' => 'string',
        ],
        'Zend\Filter\StringToUpper' => [
            'encoding' => 'string',
        ],
        'Zend\Filter\StringTrim' => [
            'charlist' => 'string',
        ],
        'Zend\Filter\StripNewlines' => [],
        'Zend\Filter\StripTags' => [
            'allowAttribs' => 'string',
            'allowTags' => 'string',
        ],
        'Zend\Filter\ToInt' => [],
        'Zend\Filter\ToNull' => [
            'type' => 'int|string',
        ],
        'Zend\Filter\UriNormalize' => [
            'defaultscheme' => 'string',
            'enforcedscheme' => 'string',
        ],
        'Zend\Filter\Word\CamelCaseToDash' => [],
        'Zend\Filter\Word\CamelCaseToSeparator' => [
            'separator' => 'string',
        ],
        'Zend\Filter\Word\CamelCaseToUnderscore' => [],
        'Zend\Filter\Word\DashToCamelCase' => [],
        'Zend\Filter\Word\DashToSeparator' => [
            'separator' => 'string',
        ],
        'Zend\Filter\Word\DashToUnderscore' => [],
        'Zend\Filter\Word\SeparatorToCamelCase' => [
            'separator' => 'string',
        ],
        'Zend\Filter\Word\SeparatorToDash' => [
            'separator' => 'string',
        ],
        'Zend\Filter\Word\SeparatorToSeparator' => [
            'searchseparator' => 'string',
            'replacementseparator' => 'string',
        ],
        'Zend\Filter\Word\UnderscoreToCamelCase' => [],
        'Zend\Filter\Word\UnderscoreToDash' => [],
        'Zend\Filter\Word\UnderscoreToSeparator' => [
            'separator' => 'string',
        ],
    ],

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
    'validator_metadata' => [
        '__all__' => [
            'breakchainonfailure' => 'bool',
            'message' => 'string',
            'messagelength' => 'int',
            'valueobscured' => 'bool',
            'translatortextdomain' => 'string',
            'translatorenabled' => 'bool',
        ],
        'Zend\Validator\Barcode\Codabar' => [],
        'Zend\Validator\Barcode\Code128' => [],
        'Zend\Validator\Barcode\Code25interleaved' => [],
        'Zend\Validator\Barcode\Code25' => [],
        'Zend\Validator\Barcode\Code39ext' => [],
        'Zend\Validator\Barcode\Code39' => [],
        'Zend\Validator\Barcode\Code93ext' => [],
        'Zend\Validator\Barcode\Code93' => [],
        'Zend\Validator\Barcode\Ean12' => [],
        'Zend\Validator\Barcode\Ean13' => [],
        'Zend\Validator\Barcode\Ean14' => [],
        'Zend\Validator\Barcode\Ean18' => [],
        'Zend\Validator\Barcode\Ean2' => [],
        'Zend\Validator\Barcode\Ean5' => [],
        'Zend\Validator\Barcode\Ean8' => [],
        'Zend\Validator\Barcode\Gtin12' => [],
        'Zend\Validator\Barcode\Gtin13' => [],
        'Zend\Validator\Barcode\Gtin14' => [],
        'Zend\Validator\Barcode\Identcode' => [],
        'Zend\Validator\Barcode\Intelligentmail' => [],
        'Zend\Validator\Barcode\Issn' => [],
        'Zend\Validator\Barcode\Itf14' => [],
        'Zend\Validator\Barcode\Leitcode' => [],
        'Zend\Validator\Barcode' => [
            'adapter' => 'string', // this is the validator adapter name to use
            'useChecksum' => 'bool',
        ],
        'Zend\Validator\Barcode\Planet' => [],
        'Zend\Validator\Barcode\Postnet' => [],
        'Zend\Validator\Barcode\Royalmail' => [],
        'Zend\Validator\Barcode\Sscc' => [],
        'Zend\Validator\Barcode\Upca' => [],
        'Zend\Validator\Barcode\Upce' => [],
        'Zend\Validator\Between' => [
            'inclusive' => 'bool',
            'max' => 'int',
            'min' => 'int',
        ],
        'Zend\Validator\Bitwise' => [
            'control' => 'int',
            'operator' => 'string',
            'strict' => 'bool',
        ],
        'Zend\Validator\Callback' => [
            'callback' => 'string',
        ],
        'Zend\Validator\CreditCard' => [
            'type' => 'string',
            'service' => 'string',
        ],
        'Zend\Validator\Csrf' => [
            'name' => 'string',
            'salt' => 'string',
            'timeout' => 'int',
        ],
        'Zend\Validator\Date' => [
            'format' => 'string',
        ],
        'Zend\Validator\DateStep' => [
            'format' => 'string',
            'basevalue' => 'string|int',
        ],
        'Zend\Validator\Db\NoRecordExists' => [
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Zend\Validator\Db\RecordExists' => [
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'ZF\ContentValidation\Validator\DbNoRecordExists' => [
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'ZF\ContentValidation\Validator\DbRecordExists' => [
            'adapter' => 'string',
            'table' => 'string',
            'schema' => 'string',
            'field' => 'string',
            'exclude' => 'string',
        ],
        'Zend\Validator\Digits' => [],
        'Zend\Validator\EmailAddress' => [
            'allow' => 'int',
            'useMxCheck' => 'bool',
            'useDeepMxCheck' => 'bool',
            'useDomainCheck' => 'bool',
        ],
        'Zend\Validator\Explode' => [
            'valuedelimiter' => 'string',
            'breakonfirstfailure' => 'bool',
        ],
        'Zend\Validator\File\Count' => [
            'max' => 'int',
            'min' => 'int',
        ],
        'Zend\Validator\File\Crc32' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'crc32' => 'string',
        ],
        'Zend\Validator\File\ExcludeExtension' => [
            'case' => 'bool',
            'extension' => 'string',
        ],
        'Zend\Validator\File\ExcludeMimeType' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Zend\Validator\File\Exists' => [
            'directory' => 'string',
        ],
        'Zend\Validator\File\Extension' => [
            'case' => 'bool',
            'extension' => 'string',
        ],
        'Zend\Validator\File\FilesSize' => [
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ],
        'Zend\Validator\File\Hash' => [
            'algorithm' => 'string',
            'hash' => 'string',
        ],
        'Zend\Validator\File\ImageSize' => [
            'maxHeight' => 'int',
            'minHeight' => 'int',
            'maxWidth' => 'int',
            'minWidth' => 'int',
        ],
        'Zend\Validator\File\IsCompressed' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Zend\Validator\File\IsImage' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Zend\Validator\File\Md5' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'md5' => 'string',
        ],
        'Zend\Validator\File\MimeType' => [
            'disableMagicFile' => 'bool',
            'magicFile' => 'string',
            'enableHeaderCheck' => 'bool',
            'mimeType' => 'string',
        ],
        'Zend\Validator\File\NotExists' => [
            'directory' => 'string',
        ],
        'Zend\Validator\File\Sha1' => [
            'algorithm' => 'string',
            'hash' => 'string',
            'sha1' => 'string',
        ],
        'Zend\Validator\File\Size' => [
            'max' => 'int',
            'min' => 'int',
            'size' => 'int',
            'useByteString' => 'bool',
        ],
        'Zend\Validator\File\UploadFile' => [],
        'Zend\Validator\File\Upload' => [],
        'Zend\Validator\File\WordCount' => [
            'max' => 'int',
            'min' => 'int',
        ],
        'Zend\Validator\GreaterThan' => [
            'inclusive' => 'bool',
            'min' => 'int',
        ],
        'Zend\Validator\Hex' => [],
        'Zend\Validator\Hostname' => [
            'allow' => 'int',
            'useIdnCheck' => 'bool',
            'useTldCheck' => 'bool',
        ],
        'Zend\Validator\Iban' => [
            'country_code' => 'string',
            'allow_non_sepa' => 'bool',
        ],
        'Zend\Validator\Identical' => [
            'literal' => 'bool',
            'strict' => 'bool',
            'token' => 'string',
        ],
        'Zend\Validator\InArray' => [
            'strict' => 'bool',
            'recursive' => 'bool',
        ],
        'Zend\Validator\Ip' => [
            'allowipv4' => 'bool',
            'allowipv6' => 'bool',
            'allowipvfuture' => 'bool',
            'allowliteral' => 'bool',
        ],
        'Zend\Validator\Isbn' => [
            'type' => 'string',
            'separator' => 'string',
        ],
        'Zend\Validator\IsInstanceOf' => [
            'classname' => 'string',
        ],
        'Zend\Validator\LessThan' => [
            'inclusive' => 'bool',
            'max' => 'int',
        ],
        'Zend\Validator\NotEmpty' => [
            'type' => 'int',
        ],
        'Zend\Validator\Regex' => [
            'pattern' => 'string',
        ],
        'Zend\Validator\Sitemap\Changefreq' => [],
        'Zend\Validator\Sitemap\Lastmod' => [],
        'Zend\Validator\Sitemap\Loc' => [],
        'Zend\Validator\Sitemap\Priority' => [],
        'Zend\Validator\Step' => [
            'baseValue' => 'int|float',
            'step' => 'float',
        ],
        'Zend\Validator\StringLength' => [
            'max' => 'int',
            'min' => 'int',
            'encoding' => 'string',
        ],
        'Zend\Validator\Uri' => [
            'allowAbsolute' => 'bool',
            'allowRelative' => 'bool',
        ],
        'Zend\I18n\Validator\Alnum' => [
            'allowwhitespace' => 'bool',
        ],
        'Zend\I18n\Validator\Alpha' => [
            'allowwhitespace' => 'bool',
        ],
        'Zend\I18n\Validator\DateTime' => [
            'calendar' => 'int',
            'datetype' => 'int',
            'pattern' => 'string',
            'timetype' => 'int',
            'timezone' => 'string',
            'locale' => 'string',
        ],
        'Zend\I18n\Validator\Float' => [
            'locale' => 'string',
        ],
        'Zend\I18n\Validator\Int' => [
            'locale' => 'string',
        ],
        'Zend\I18n\Validator\IsFloat' => [
            'locale' => 'string',
        ],
        'Zend\I18n\Validator\IsInt' => [
            'locale' => 'string',
        ],
        'Zend\I18n\Validator\PhoneNumber' => [
            'country' => 'string',
            'allow_possible' => 'bool',
        ],
        'Zend\I18n\Validator\PostCode' => [
            'locale' => 'string',
            'format' => 'string',
            'service' => 'string',
        ],
    ],

    'input_filters' => [
        'invokables' => [
            'ZF\Apigility\Admin\InputFilter\Authentication\BasicAuth' => 'ZF\Apigility\Admin\InputFilter\Authentication\BasicInputFilter',
            'ZF\Apigility\Admin\InputFilter\Authentication\DigestAuth' => 'ZF\Apigility\Admin\InputFilter\Authentication\DigestInputFilter',
            'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2' => 'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2InputFilter',
            'ZF\Apigility\Admin\InputFilter\Authorization' => 'ZF\Apigility\Admin\InputFilter\AuthorizationInputFilter',
            'ZF\Apigility\Admin\InputFilter\DbAdapter' => 'ZF\Apigility\Admin\InputFilter\DbAdapterInputFilter',
            'ZF\Apigility\Admin\InputFilter\ContentNegotiation' => 'ZF\Apigility\Admin\InputFilter\ContentNegotiationInputFilter',
            'ZF\Apigility\Admin\InputFilter\CreateContentNegotiation' => 'ZF\Apigility\Admin\InputFilter\CreateContentNegotiationInputFilter',

            'ZF\Apigility\Admin\InputFilter\Module' => 'ZF\Apigility\Admin\InputFilter\ModuleInputFilter',
            'ZF\Apigility\Admin\InputFilter\Version' => 'ZF\Apigility\Admin\InputFilter\VersionInputFilter',
            'ZF\Apigility\Admin\InputFilter\RestService\POST' => 'ZF\Apigility\Admin\InputFilter\RestService\PostInputFilter',
            'ZF\Apigility\Admin\InputFilter\RestService\PATCH' => 'ZF\Apigility\Admin\InputFilter\RestService\PatchInputFilter',
            'ZF\Apigility\Admin\InputFilter\RpcService\POST' => 'ZF\Apigility\Admin\InputFilter\RpcService\PostInputFilter',
            'ZF\Apigility\Admin\InputFilter\RpcService\PATCH' => 'ZF\Apigility\Admin\InputFilter\RpcService\PatchInputFilter',

            'ZF\Apigility\Admin\InputFilter\Documentation' => 'ZF\Apigility\Admin\InputFilter\DocumentationInputFilter',
        ],
        'factories' => [
            'ZF\Apigility\Admin\InputFilter\InputFilter' => 'ZF\Apigility\Admin\InputFilter\Factory\InputFilterInputFilterFactory',
        ]
    ],

    'zf-content-validation' => [
        'ZF\Apigility\Admin\Controller\HttpBasicAuthentication' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\Authentication\BasicAuth'
        ],
        'ZF\Apigility\Admin\Controller\HttpDigestAuthentication' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\Authentication\DigestAuth'
        ],
        'ZF\Apigility\Admin\Controller\OAuth2Authentication' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\Authentication\OAuth2'
        ],
        'ZF\Apigility\Admin\Controller\DbAdapter' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\DbAdapter',
        ],

        'ZF\Apigility\Admin\Controller\ContentNegotiation' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\ContentNegotiation',
            'POST' => 'ZF\Apigility\Admin\InputFilter\CreateContentNegotiation',
        ],

        'ZF\Apigility\Admin\Controller\Module' => [
            'POST' => 'ZF\Apigility\Admin\InputFilter\Module',
        ],
        'ZF\Apigility\Admin\Controller\Versioning' => [
            'PATCH' => 'ZF\Apigility\Admin\InputFilter\Version',
        ],
        'ZF\Apigility\Admin\Controller\RestService' => [
            'POST' => 'ZF\Apigility\Admin\InputFilter\RestService\POST', // for the collection
            'PATCH' => 'ZF\Apigility\Admin\InputFilter\RestService\PATCH', // for the entity
        ],
        'ZF\Apigility\Admin\Controller\RpcService' => [
            'POST' => 'ZF\Apigility\Admin\InputFilter\RpcService\POST', // for the collection
            'PATCH' => 'ZF\Apigility\Admin\InputFilter\RpcService\PATCH', // for the entity
        ],

        'ZF\Apigility\Admin\Controller\InputFilter' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\InputFilter',
        ],

        'ZF\Apigility\Admin\Controller\Documentation' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\Documentation',
        ],

        'ZF\Apigility\Admin\Controller\Authorization' => [
            'input_filter' => 'ZF\Apigility\Admin\InputFilter\Authorization',
        ],

    ],
];
