<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\EnableHalRenderCollectionsListener;
use ZF\Hal\Plugin\Hal;
use ZFTest\Apigility\Admin\RouteAssetsTrait;

class EnableHalRenderCollectionsListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerParam()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn(null)
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchControllerParamDoesNotMatchAdminNamespace()
    {
        $listener = new EnableHalRenderCollectionsListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn('Foo\Bar\Baz')
            ->shouldBeCalled();

        $this->event->getTarget()->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerEnablesCollectionRenderingOnHalPluginWhenControllerMatchesAdminNamespace()
    {
        $listener = new EnableHalRenderCollectionsListener();

        $plugin = $this->prophesize(Hal::class);
        $plugin->setRenderCollections(true)->shouldBeCalled();

        $helpers = $this->prophesize(ContainerInterface::class);
        $helpers->get('Hal')->will([$plugin, 'reveal'])->shouldBeCalled();

        $services = $this->prophesize(ContainerInterface::class);
        $services->get('ViewHelperManager')->will([$helpers, 'reveal'])->shouldBeCalled();

        $app = $this->prophesize(ApplicationInterface::class);
        $app->getServiceManager()->will([$services, 'reveal'])->shouldBeCalled();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller')
            ->willReturn('ZF\Apigility\Admin\Model\RestServiceModel')
            ->shouldBeCalled();

        $this->event->getTarget()->will([$app, 'reveal'])->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
