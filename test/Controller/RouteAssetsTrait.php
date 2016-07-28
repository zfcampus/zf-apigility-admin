<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack as V2TreeRouteStack;
use Zend\Router\RouteMatch;
use Zend\Router\TreeRouteStack;

trait RouteAssetsTrait
{
    public function createRouteMatch(array $params = [])
    {
        $class = class_exists(V2RouteMatch::class) ? V2RouteMatch::class : RouteMatch::class;
        return new $class($params);
    }

    public function createRouter(array $config = [])
    {
        $class = class_exists(V2TreeRouteStack::class) ? V2TreeRouteStack::class : TreeRouteStack::class;
        $config['routes']['zf-apigility']['type'] = 'literal';
        $config['routes']['zf-apigility']['options'] = ['route' => '/apigility'];
        return $class::factory($config);
    }
}
