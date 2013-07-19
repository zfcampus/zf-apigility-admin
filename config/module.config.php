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
            'zf-api-first-admin/app/app' => __DIR__ . '/../view/app/app.phtml',
        )
    ),

    'controllers' => array(
        'invokables' => array(
            'ZFApiFirstAdmin\Controller\App' => 'ZFApiFirstAdmin\Controller\AppController',
        )
    ),

    'router' => array(
        'routes' => array(
            'zf-api-first-admin' => array(
                'type'  => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'ZFApiFirstAdmin\Controller\App',
                        'action'     => 'app'
                    )
                )
            )
        )
    ),

);