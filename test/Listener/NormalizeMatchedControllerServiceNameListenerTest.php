<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\NormalizeMatchedControllerServiceNameListener;
use ZFTest\Apigility\Admin\RouteAssetsTrait;

class NormalizeMatchedControllerServiceNameListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoControllerServiceName()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', Argument::type('string'))
            ->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedControllerServiceName()
    {
        $listener = new NormalizeMatchedControllerServiceNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn('Foo-Bar-BazController')
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', 'Foo\\Bar\\BazController')
            ->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
