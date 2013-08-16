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
            'ZF\ApiFirstAdmin\Controller\App' => 'ZF\ApiFirstAdmin\Controller\AppController',
        )
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
                            ),
                            'module' => array(
                                'type' => 'segment',
                                'options' => array(
                                    'route' => '/module[/:module]',
                                    'defaults' => array(
                                        'controller' => 'ZF\ApiFirstAdmin\Controller\ModuleResource',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),

    'zf-hal' => array(
        'metadata_map' => array(
            'ZF\ApiFirstAdmin\Model\ModuleMetadata' => array(
                'hydrator'        => 'ArraySerializable',
                'identifier_name' => 'module',
                'route_name'      => 'zf-api-first-admin/api/module',
            ),
        ),
    ),

    'zf-rest' => array(
        'ZF\ApiFirstAdmin\Controller\ModuleResource' => array(
            'listener'                => 'ZF\ApiFirstAdmin\Model\ApiFirstModuleListener',
            'route_name'              => 'zf-api-first-admin/api/module',
            'identifier_name'         => 'module',
            'resource_http_options'   => array('GET'),
            'collection_http_options' => array('GET', 'POST'),
        ),
    ),
);
