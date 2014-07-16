<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ServiceManager;
use Zend\View\HelperPluginManager;
use ZF\Apigility\Admin\Module;
use ZF\Hal\Plugin\Hal;

class ModuleTest extends TestCase
{
    public function setUp()
    {
        $this->services = new ServiceManager();
        $this->module = new Module();
    }

    public function setupServiceChain()
    {
        $this->hal = new Hal();
        $this->helpers = new HelperPluginManager();
        $this->helpers->setService('Hal', $this->hal);
        $this->helpers->setServiceLocator($this->services);
        $this->services->setService('ViewHelperManager', $this->helpers);
        $this->application = new TestAsset\Application();
        $this->application->setServiceManager($this->services);
    }

    public function testRouteListenerDoesNothingIfNoRouteMatches()
    {
        $event = new MvcEvent();
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerDoesNothingIfRouteMatchesDoNotContainController()
    {
        $matches = new RouteMatch(array());
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerDoesNothingIfRouteMatchControllerIsNotRelevant()
    {
        $matches = new RouteMatch(array(
            'controller' => 'Foo\Bar',
        ));
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $this->assertNull($this->module->onRoute($event));
    }

    public function testRouteListenerModifiesHalPluginToRenderCollectionsIfControllerIsRelevant()
    {
        $this->setupServiceChain();
        $this->hal->setRenderCollections(false);

        $matches = new RouteMatch(array(
            'controller' => 'ZF\Apigility\Admin\Foo\Controller',
        ));
        $event = new MvcEvent();
        $event->setRouteMatch($matches);
        $event->setTarget($this->application);

        $this->assertNull($this->module->onRoute($event));
        $this->assertTrue($this->hal->getRenderCollections());
    }
}
