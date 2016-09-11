<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin;

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'zf-apigility-admin' => [
        // path_spec defines whether modules should be created using PSR-0
        //     or PSR-4 module structure; the default is to use PSR-0.
        //     Valid values are:
        //     - ZF\Apigility\Admin\Model\ModulePathSpec::PSR_0 ("psr-0")
        //     - ZF\Apigility\Admin\Model\ModulePathSpec::PSR_4 ("psr-4")
        // 'path_spec' => 'psr-0',
    ],

    'service_manager' => [
        'factories' => [
            // @codingStandardsIgnoreStart
            Listener\CryptFilterListener::class                           => InvokableFactory::class,
            Listener\DisableHttpCacheListener::class                      => InvokableFactory::class,
            Listener\EnableHalRenderCollectionsListener::class            => InvokableFactory::class,
            Listener\InjectModuleResourceLinksListener::class             => Listener\InjectModuleResourceLinksListenerFactory::class,
            Listener\NormalizeMatchedControllerServiceNameListener::class => InvokableFactory::class,
            Listener\NormalizeMatchedInputFilterNameListener::class       => InvokableFactory::class,
            Model\AuthenticationModel::class                              => Model\AuthenticationModelFactory::class,
            Model\AuthorizationModelFactory::class                        => Model\AuthorizationModelFactoryFactory::class,
            Model\ContentNegotiationModel::class                          => Model\ContentNegotiationModelFactory::class,
            Model\ContentNegotiationResource::class                       => Model\ContentNegotiationResourceFactory::class,
            Model\DbAdapterModel::class                                   => Model\DbAdapterModelFactory::class,
            Model\DbAdapterResource::class                                => Model\DbAdapterResourceFactory::class,
            Model\DbAutodiscoveryModel::class                             => Model\DbAutodiscoveryModelFactory::class,
            Model\DoctrineAdapterModel::class                             => Model\DoctrineAdapterModelFactory::class,
            Model\DoctrineAdapterResource::class                          => Model\DoctrineAdapterResourceFactory::class,
            Model\DocumentationModel::class                               => Model\DocumentationModelFactory::class,
            Model\FiltersModel::class                                     => Model\FiltersModelFactory::class,
            Model\HydratorsModel::class                                   => Model\HydratorsModelFactory::class,
            Model\InputFilterModel::class                                 => Model\InputFilterModelFactory::class,
            Model\ModuleModel::class                                      => Model\ModuleModelFactory::class,
            Model\ModulePathSpec::class                                   => Model\ModulePathSpecFactory::class,
            Model\ModuleResource::class                                   => Model\ModuleResourceFactory::class,
            Model\ModuleVersioningModelFactory::class                     => Model\ModuleVersioningModelFactoryFactory::class,
            Model\RestServiceModelFactory::class                          => Model\RestServiceModelFactoryFactory::class,
            Model\RestServiceResource::class                              => Model\RestServiceResourceFactory::class,
            Model\RpcServiceModelFactory::class                           => Model\RpcServiceModelFactoryFactory::class,
            Model\RpcServiceResource::class                               => Model\RpcServiceResourceFactory::class,
            Model\ValidatorMetadataModel::class                           => Model\ValidatorMetadataModelFactory::class,
            Model\ValidatorsModel::class                                  => Model\ValidatorsModelFactory::class,
            Model\VersioningModelFactory::class                           => Model\VersioningModelFactoryFactory::class,
            // @codingStandardsIgnoreEnd
        ],
    ],

    'controllers' => [
        'aliases' => [
            Controller\App::class                      => Controller\AppController::class,
            Controller\Authentication::class           => Controller\AuthenticationController::class,
            Controller\Authorization::class            => Controller\AuthorizationController::class,
            Controller\CacheEnabled::class             => Controller\CacheEnabledController::class,
            Controller\Config::class                   => Controller\ConfigController::class,
            Controller\FsPermissions::class            => Controller\FsPermissionsController::class,
            Controller\HttpBasicAuthentication::class  => Controller\Authentication::class,
            Controller\HttpDigestAuthentication::class => Controller\Authentication::class,
            Controller\ModuleConfig::class             => Controller\ModuleConfigController::class,
            Controller\ModuleCreation::class           => Controller\ModuleCreationController::class,
            Controller\OAuth2Authentication::class     => Controller\Authentication::class,
            Controller\Package::class                  => Controller\PackageController::class,
            Controller\Source::class                   => Controller\SourceController::class,
            Controller\Versioning::class               => Controller\VersioningController::class,
        ],
        'factories' => [
            Controller\ApigilityVersionController::class => InvokableFactory::class,
            Controller\AppController::class            => InvokableFactory::class,
            Controller\AuthenticationController::class => Controller\AuthenticationControllerFactory::class,
            Controller\AuthenticationType::class       => Controller\AuthenticationTypeControllerFactory::class,
            Controller\AuthorizationController::class  => Controller\AuthorizationControllerFactory::class,
            Controller\CacheEnabledController::class   => InvokableFactory::class,
            Controller\ConfigController::class         => Controller\ConfigControllerFactory::class,
            Controller\Dashboard::class                => Controller\DashboardControllerFactory::class,
            Controller\DbAutodiscovery::class          => Controller\DbAutodiscoveryControllerFactory::class,
            Controller\Documentation::class            => Controller\DocumentationControllerFactory::class,
            Controller\Filters::class                  => Controller\FiltersControllerFactory::class,
            Controller\FsPermissionsController::class  => InvokableFactory::class,
            Controller\Hydrators::class                => Controller\HydratorsControllerFactory::class,
            Controller\InputFilter::class              => Controller\InputFilterControllerFactory::class,
            Controller\ModuleConfigController::class   => Controller\ModuleConfigControllerFactory::class,
            Controller\ModuleCreationController::class => Controller\ModuleCreationControllerFactory::class,
            Controller\PackageController::class        => InvokableFactory::class,
            Controller\SettingsDashboard::class        => Controller\DashboardControllerFactory::class,
            Controller\SourceController::class         => Controller\SourceControllerFactory::class,
            Controller\Strategy::class                 => Controller\StrategyControllerFactory::class,
            Controller\Validators::class               => Controller\ValidatorsControllerFactory::class,
            Controller\VersioningController::class     => Controller\VersioningControllerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'zf-apigility' => [
                'child_routes' => [
                    'ui' => [
                        'type'  => 'Literal',
                        'options' => [
                            'route' => '/ui',
                            'defaults' => [
                                'controller' => Controller\App::class,
                                'action'     => 'app',
                            ],
                        ],
                    ],
                    'api' => [
                        'type' => 'Literal',
                        'options' => [
                            'route' => '/api',
                            'defaults' => [
                                'is_apigility_admin_api' => true,
                                'action'                 => false,
                            ],
                        ],
                        'may_terminate' => false,
                        'child_routes' => [
                            'apigility-version' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/apigility-version',
                                    'defaults' => [
                                        'controller' => Controller\ApigilityVersionController::class,
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'controller' => Controller\Dashboard::class,
                                        'action'     => 'dashboard',
                                    ],
                                ],
                            ],
                            'settings-dashboard' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/settings-dashboard',
                                    'defaults' => [
                                        'controller' => Controller\SettingsDashboard::class,
                                        'action'     => 'settingsDashboard',
                                    ],
                                ],
                            ],
                            'strategy' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/strategy/:strategy_name',
                                    'defaults' => [
                                        'controller' => Controller\Strategy::class,
                                        'action'     => 'exists',
                                    ],
                                ],
                            ],
                            'cache-enabled' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/cache-enabled',
                                    'defaults' => [
                                        'controller' => Controller\CacheEnabled::class,
                                        'action'     => 'cacheEnabled',
                                    ],
                                ],
                            ],
                            'fs-permissions' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/fs-permissions',
                                    'defaults' => [
                                        'controller' => Controller\FsPermissions::class,
                                        'action'     => 'fsPermissions',
                                    ],
                                ],
                            ],
                            'config' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/config',
                                    'defaults' => [
                                        'controller' => Controller\Config::class,
                                        'action'     => 'process',
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'module' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/module',
                                            'defaults' => [
                                                'controller' => Controller\ModuleConfig::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'source' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/source',
                                    'defaults' => [
                                        'controller' => Controller\Source::class,
                                        'action'     => 'source',
                                    ],
                                ],
                            ],
                            'filters' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/filters',
                                    'defaults' => [
                                        'controller' => Controller\Filters::class,
                                        'action'     => 'filters',
                                    ],
                                ],
                            ],
                            'hydrators' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/hydrators',
                                    'defaults' => [
                                        'controller' => Controller\Hydrators::class,
                                        'action'     => 'hydrators',
                                    ],
                                ],
                            ],
                            'validators' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/validators',
                                    'defaults' => [
                                        'controller' => Controller\Validators::class,
                                        'action'     => 'validators',
                                    ],
                                ],
                            ],
                            'module-enable' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/module.enable',
                                    'defaults' => [
                                        'controller' => Controller\ModuleCreation::class,
                                        'action'     => 'apiEnable',
                                    ],
                                ],
                            ],
                            'versioning' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/versioning',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'versioning',
                                    ],
                                ],
                            ],
                            'default-version' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/default-version',
                                    'defaults' => [
                                        'controller' => Controller\Versioning::class,
                                        'action'     => 'defaultVersion',
                                    ],
                                ],
                            ],
                            'module' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/module[/:name]',
                                    'defaults' => [
                                        'controller' => Controller\Module::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'authentication' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/authentication',
                                            'defaults' => [
                                                'controller' => Controller\Authentication::class,
                                                'action'     => 'mapping',
                                            ],
                                        ],
                                    ],
                                    'authorization' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/authorization',
                                            'defaults' => [
                                                'controller' => Controller\Authorization::class,
                                                'action'     => 'authorization',
                                            ],
                                        ],
                                    ],
                                    'rpc-service' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/rpc[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => Controller\RpcService::class,
                                                'controller_type' => 'rpc',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:http_method[/:http_direction]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'rest-service' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/rest[/:controller_service_name]',
                                            'defaults' => [
                                                'controller' => Controller\RestService::class,
                                                'controller_type' => 'rest',
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'input-filter' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/input-filter[/:input_filter_name]',
                                                    'defaults' => [
                                                        'controller' => Controller\InputFilter::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                            'doc' => [
                                                'type' => 'Segment',
                                                'options' => [
                                                    'route' => '/doc', // [/:rest_resource_type[/:http_method[/:http_direction]]]
                                                    'defaults' => [
                                                        'controller' => Controller\Documentation::class,
                                                        'action'     => 'index',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    'db-autodiscovery' => [
                                        'type' => 'Segment',
                                        'options' => [
                                            'route' => '/:version/autodiscovery/:adapter_name',
                                            'defaults' => [
                                                'controller' => Controller\DbAutodiscovery::class,
                                                'action' => 'discover',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'authentication' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/authentication[/:authentication_adapter]',
                                    'defaults' => [
                                        'action'     => 'authentication',
                                        'controller' => Controller\Authentication::class,
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'oauth2' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/oauth2',
                                            'defaults' => [
                                                'controller' => Controller\OAuth2Authentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-basic' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/http-basic',
                                            'defaults' => [
                                                'controller' => Controller\HttpBasicAuthentication::class,
                                            ],
                                        ],
                                    ],
                                    'http-digest' => [
                                        'type' => 'Literal',
                                        'options' => [
                                            'route' => '/http-digest',
                                            'defaults' => [
                                                'controller' => Controller\HttpDigestAuthentication::class,
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'db-adapter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/db-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DbAdapter::class,
                                    ],
                                ],
                            ],
                            'doctrine-adapter' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/doctrine-adapter[/:adapter_name]',
                                    'defaults' => [
                                        'controller' => Controller\DoctrineAdapter::class,
                                    ],
                                ],
                            ],
                            'content-negotiation' => [
                                'type' => 'Segment',
                                'options' => [
                                    'route' => '/content-negotiation[/:content_name]',
                                    'defaults' => [
                                        'controller' => Controller\ContentNegotiation::class,
                                    ],
                                ],
                            ],
                            'package' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/package',
                                    'defaults' => [
                                        'controller' => Controller\Package::class,
                                        'action'     => 'index',
                                    ],
                                ],
                            ],
                            'authentication-type' => [
                                'type' => 'Literal',
                                'options' => [
                                    'route' => '/auth-type',
                                    'defaults' => [
                                        'controller' => Controller\AuthenticationType::class,
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
            Controller\ApigilityVersionController::class => 'Json',
            Controller\Authentication::class           => 'HalJson',
            Controller\AuthenticationType::class       => 'Json',
            Controller\Authorization::class            => 'HalJson',
            Controller\CacheEnabled::class             => 'Json',
            Controller\ContentNegotiation::class       => 'HalJson',
            Controller\Dashboard::class                => 'HalJson',
            Controller\DbAdapter::class                => 'HalJson',
            Controller\DbAutodiscovery::class          => 'Json',
            Controller\DoctrineAdapter::class          => 'HalJson',
            Controller\Documentation::class            => 'HalJson',
            Controller\Filters::class                  => 'Json',
            Controller\FsPermissions::class            => 'Json',
            Controller\HttpBasicAuthentication::class  => 'HalJson',
            Controller\HttpDigestAuthentication::class => 'HalJson',
            Controller\Hydrators::class                => 'Json',
            Controller\InputFilter::class              => 'HalJson',
            Controller\Module::class                   => 'HalJson',
            Controller\ModuleCreation::class           => 'HalJson',
            Controller\OAuth2Authentication::class     => 'HalJson',
            Controller\Package::class                  => 'Json',
            Controller\RestService::class              => 'HalJson',
            Controller\RpcService::class               => 'HalJson',
            Controller\SettingsDashboard::class        => 'HalJson',
            Controller\Source::class                   => 'Json',
            Controller\Strategy::class                 => 'Json',
            Controller\Validators::class               => 'Json',
            Controller\Versioning::class               => 'Json',
        ],
        'accept_whitelist' => [
            Controller\ApigilityVersionController::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Authorization::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Documentation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\FsPermissions::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpBasicAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\OAuth2Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Strategy::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Validators::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Versioning::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RestService::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class => [
                'application/json',
                'application/*+json',
            ],
        ],
        'content_type_whitelist' => [
            Controller\Authorization::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\CacheEnabled::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ContentNegotiation::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Dashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DbAutodiscovery::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\DoctrineAdapter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Filters::class => [
                'application/json',
            ],
            Controller\FsPermissions::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Hydrators::class => [
                'application/json',
            ],
            Controller\HttpBasicAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\HttpDigestAuthentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\InputFilter::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Module::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\ModuleCreation::class => [
                'application/json',
            ],
            Controller\OAuth2Authentication::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\SettingsDashboard::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\Source::class => [
                'application/json',
            ],
            Controller\Strategy::class => [
                'application/json',
            ],
            Controller\Validators::class => [
                'application/json',
            ],
            Controller\Versioning::class => [
                'application/json',
            ],
            Controller\RestService::class => [
                'application/json',
                'application/*+json',
            ],
            Controller\RpcService::class => [
                'application/json',
                'application/*+json',
            ],
        ],
    ],

    'zf-hal' => [
        'metadata_map' => [
            Model\AuthenticationEntity::class => [
                'hydrator'        => 'ArraySerializable',
            ],
            Model\AuthorizationEntity::class => [
                'hydrator'        => 'ArraySerializable',
            ],
            Model\ContentNegotiationEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'content_name',
                'entity_identifier_name' => 'content_name',
                'route_name'      => 'zf-apigility/api/content-negotiation',
            ],
            Model\DbConnectedRestServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rest-service',
            ],
            Model\DbAdapterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility/api/db-adapter',
            ],
            Model\DoctrineAdapterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'adapter_name',
                'entity_identifier_name' => 'adapter_name',
                'route_name'      => 'zf-apigility/api/doctrine-adapter',
            ],
            Model\InputFilterCollection::class => [
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\InputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
            ],
            Model\ModuleEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'name',
                'entity_identifier_name' => 'name',
                'route_name'      => 'zf-apigility/api/module',
            ],
            Model\RestInputFilterCollection::class => [
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RestInputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rest-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\DocumentationEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'rest_documentation',
                'entity_identifier_name' => 'rest_documentation',
                'route_name'      => 'zf-apigility/api/module/rest-service/rest-doc',
            ],
            Model\RestServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rest-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rest-service/input-filter',
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rest-service/doc',
                        ],
                    ],
                ],
            ],
            Model\RpcInputFilterCollection::class => [
                'route_name'      => 'zf-apigility/api/module/rpc-service/input-filter',
                'is_collection'   => true,
                'collection_name' => 'input_filter',
                'route_identifier_name' => 'input_filter_name',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcInputFilterEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'input_filter_name',
                'route_name'      => 'zf-apigility/api/module/rpc-service/input-filter',
                'entity_identifier_name' => 'input_filter_name',
            ],
            Model\RpcServiceEntity::class => [
                'hydrator'        => 'ArraySerializable',
                'route_identifier_name' => 'controller_service_name',
                'entity_identifier_name' => 'controller_service_name',
                'route_name'      => 'zf-apigility/api/module/rpc-service',
                'links'           => [
                    [
                        'rel' => 'input_filter',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rpc-service/input-filter',
                        ],
                    ],
                    [
                        'rel' => 'documentation',
                        'route' => [
                            'name' => 'zf-apigility/api/module/rpc-service/doc',
                        ],
                    ],
                ],
            ],
        ],
    ],

    'zf-rest' => [
        Controller\ContentNegotiation::class => [
            'listener'                => Model\ContentNegotiationResource::class,
            'route_name'              => 'zf-apigility/api/content-negotiation',
            'route_identifier_name'   => 'content_name',
            'entity_class'            => Model\ContentNegotiationEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'selectors',
        ],
        Controller\DbAdapter::class => [
            'listener'                => Model\DbAdapterResource::class,
            'route_name'              => 'zf-apigility/api/db-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DbAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'db_adapter',
        ],
        Controller\DoctrineAdapter::class => [
            'listener'                => Model\DoctrineAdapterResource::class,
            'route_name'              => 'zf-apigility/api/doctrine-adapter',
            'route_identifier_name'   => 'adapter_name',
            'entity_class'            => Model\DoctrineAdapterEntity::class,
            'entity_http_methods'     => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods' => ['GET'],
            'collection_name'         => 'doctrine_adapter',
        ],
        Controller\Module::class => [
            'listener'                => Model\ModuleResource::class,
            'route_name'              => 'zf-apigility/api/module',
            'route_identifier_name'   => 'name',
            'entity_class'            => Model\ModuleEntity::class,
            'entity_http_methods'     => ['GET', 'DELETE'],
            'collection_http_methods' => ['GET', 'POST'],
            'collection_name'         => 'module',
        ],
        Controller\RpcService::class => [
            'listener'                   => Model\RpcServiceResource::class,
            'route_name'                 => 'zf-apigility/api/module/rpc-service',
            'entity_class'               => Model\RpcServiceEntity::class,
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rpc',
            'collection_query_whitelist' => ['version'],
        ],
        Controller\RestService::class => [
            'listener'                   => Model\RestServiceResource::class,
            'route_name'                 => 'zf-apigility/api/module/rest-service',
            'entity_class'               => Model\RestServiceEntity::class,
            'route_identifier_name'      => 'controller_service_name',
            'entity_http_methods'        => ['GET', 'PATCH', 'DELETE'],
            'collection_http_methods'    => ['GET', 'POST'],
            'collection_name'            => 'rest',
            'collection_query_whitelist' => ['version'],
        ],
    ],

    'zf-rpc' => [
        Controller\ApigilityVersionController::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/apigility-version',
        ],
        Controller\Authentication::class => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication',
        ],
        Controller\AuthenticationType::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/authentication-type',
        ],
        Controller\Authorization::class => [
            'http_methods' => ['GET', 'PATCH', 'PUT'],
            'route_name'   => 'zf-apigility/api/module/authorization',
        ],
        Controller\CacheEnabled::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/cache-enabled',
        ],
        Controller\Config::class => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'zf-apigility/api/config',
        ],
        Controller\Dashboard::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/dashboard',
        ],
        Controller\DbAutodiscovery::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/module/db-autodiscovery',
        ],
        Controller\Documentation::class => [
            'http_methods' => ['GET', 'PATCH', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/rest-service/rest-doc',
        ],
        Controller\Filters::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/filters',
        ],
        Controller\FsPermissions::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/fs-permissions',
        ],
        Controller\HttpBasicAuthentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/http-basic',
        ],
        Controller\HttpDigestAuthentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/http-digest',
        ],
        Controller\Hydrators::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/hydrators',
        ],
        Controller\InputFilter::class => [
            'http_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
            'route_name'   => 'zf-apigility/api/rpc-service/input-filter',
        ],
        Controller\ModuleConfig::class => [
            'http_methods' => ['GET', 'PATCH'],
            'route_name'   => 'zf-apigility/api/config/module',
        ],
        Controller\ModuleCreation::class => [
            'http_methods' => ['PUT'],
            'route_name'   => 'zf-apigility/api/module-enable',
        ],
        Controller\OAuth2Authentication::class => [
            'http_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
            'route_name'   => 'zf-apigility/api/authentication/oauth2',
        ],
        Controller\SettingsDashboard::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/settings-dashboard',
        ],
        Controller\Source::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/source',
        ],
        Controller\Validators::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/validators',
        ],
        Controller\Versioning::class => [
            'http_methods' => ['PATCH'],
            'route_name'   => 'zf-apigility/api/versioning',
        ],
        Controller\Strategy::class => [
            'http_methods' => ['GET'],
            'route_name'   => 'zf-apigility/api/strategy',
        ],
        Controller\Package::class => [
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
        'Zend\Validator\Uuid' => [],
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
        'aliases' => [
            InputFilter\Authentication\BasicAuth::class  => InputFilter\Authentication\BasicInputFilter::class,
            InputFilter\Authentication\DigestAuth::class => InputFilter\Authentication\DigestInputFilter::class,
            InputFilter\Authentication\OAuth2::class     => InputFilter\Authentication\OAuth2InputFilter::class,
            InputFilter\Authorization::class             => InputFilter\AuthorizationInputFilter::class,
            InputFilter\ContentNegotiation::class        => InputFilter\ContentNegotiationInputFilter::class,
            InputFilter\CreateContentNegotiation::class  => InputFilter\CreateContentNegotiationInputFilter::class,
            InputFilter\DbAdapter::class                 => InputFilter\DbAdapterInputFilter::class,
            InputFilter\Documentation::class             => InputFilter\DocumentationInputFilter::class,
            InputFilter\Module::class                    => InputFilter\ModuleInputFilter::class,
            InputFilter\RestService\PATCH::class         => InputFilter\RestService\PatchInputFilter::class,
            InputFilter\RestService\POST::class          => InputFilter\RestService\PostInputFilter::class,
            InputFilter\RpcService\PATCH::class          => InputFilter\RpcService\PatchInputFilter::class,
            InputFilter\RpcService\POST::class           => InputFilter\RpcService\PostInputFilter::class,
            InputFilter\Version::class                   => InputFilter\VersionInputFilter::class,
        ],
        'factories' => [
            InputFilter\Authentication\BasicInputFilter::class     => InvokableFactory::class,
            InputFilter\Authentication\DigestInputFilter::class    => InvokableFactory::class,
            InputFilter\Authentication\OAuth2InputFilter::class    => InvokableFactory::class,
            InputFilter\AuthorizationInputFilter::class            => InvokableFactory::class,
            InputFilter\ContentNegotiationInputFilter::class       => InvokableFactory::class,
            InputFilter\CreateContentNegotiationInputFilter::class => InvokableFactory::class,
            InputFilter\DbAdapterInputFilter::class                => InvokableFactory::class,
            InputFilter\DocumentationInputFilter::class            => InvokableFactory::class,
            InputFilter\ModuleInputFilter::class                   => InvokableFactory::class,
            InputFilter\RestService\PatchInputFilter::class        => InvokableFactory::class,
            InputFilter\RestService\PostInputFilter::class         => InvokableFactory::class,
            InputFilter\RpcService\PatchInputFilter::class         => InvokableFactory::class,
            InputFilter\RpcService\PostInputFilter::class          => InvokableFactory::class,
            InputFilter\VersionInputFilter::class                  => InvokableFactory::class,

            InputFilter\InputFilter::class => InputFilter\Factory\InputFilterInputFilterFactory::class,
        ],
    ],

    'zf-content-validation' => [
        Controller\HttpBasicAuthentication::class => [
            'input_filter' => InputFilter\Authentication\BasicAuth::class,
        ],
        Controller\HttpDigestAuthentication::class => [
            'input_filter' => InputFilter\Authentication\DigestAuth::class,
        ],
        Controller\OAuth2Authentication::class => [
            'input_filter' => InputFilter\Authentication\OAuth2::class,
        ],
        Controller\DbAdapter::class => [
            'input_filter' => InputFilter\DbAdapter::class,
        ],
        Controller\ContentNegotiation::class => [
            'input_filter' => InputFilter\ContentNegotiation::class,
            'POST' => InputFilter\CreateContentNegotiation::class,
        ],
        Controller\Module::class => [
            'POST' => InputFilter\Module::class,
        ],
        Controller\Versioning::class => [
            'PATCH' => InputFilter\Version::class,
        ],
        Controller\RestService::class => [
            'POST' => InputFilter\RestService\POST::class, // for the collection
            'PATCH' => InputFilter\RestService\PATCH::class, // for the entity
        ],
        Controller\RpcService::class => [
            'POST' => InputFilter\RpcService\POST::class, // for the collection
            'PATCH' => InputFilter\RpcService\PATCH::class, // for the entity
        ],
        Controller\InputFilter::class => [
            'input_filter' => InputFilter\InputFilter::class,
        ],
        Controller\Documentation::class => [
            'input_filter' => InputFilter\Documentation::class,
        ],
        Controller\Authorization::class => [
            'input_filter' => InputFilter\Authorization::class,
        ],
    ],
];
