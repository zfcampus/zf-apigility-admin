<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin;

use Zend\Mvc\Router\Http\TreeRouteStack as V2TreeRouteStack;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\Http\TreeRouteStack;
use Zend\Router\RouteMatch;

trait RouteAssetsTrait
{
    /**
     * @param array $params
     * @return RouteMatch|V2RouteMatch
     */
    public function createRouteMatch(array $params = [])
    {
        $class = $this->getRouteMatchClass();
        return new $class($params);
    }

    /**
     * @param string Name of route match class currently available.
     */
    public function getRouteMatchClass()
    {
        return class_exists(V2RouteMatch::class) ? V2RouteMatch::class : RouteMatch::class;
    }

    public function createRouter(array $config = [])
    {
        $class = class_exists(V2TreeRouteStack::class) ? V2TreeRouteStack::class : TreeRouteStack::class;
        $config['routes']['zf-apigility']['type'] = 'literal';
        $config['routes']['zf-apigility']['options'] = ['route' => '/apigility'];
        return $class::factory($config);
    }

    /**
     * @param RouteMatch|V2RouteMatch|null
     * @return bool
     */
    public function isRouteMatch($routeMatch)
    {
        return ($routeMatch instanceof RouteMatch || $routeMatch instanceof V2RouteMatch);
    }
}
