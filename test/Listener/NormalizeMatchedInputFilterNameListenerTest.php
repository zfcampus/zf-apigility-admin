<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\NormalizeMatchedInputFilterNameListener;
use ZFTest\Apigility\Admin\RouteAssetsTrait;

class NormalizeMatchedInputFilterNameListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchHasNoInputFilterName()
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('input_filter_name')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('input_filter_name', Argument::type('string'))
            ->shouldNotBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerReplacesDashesWithBackslashesInMatchedInputFilterName()
    {
        $listener = new NormalizeMatchedInputFilterNameListener();
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->routeMatch
            ->getParam('input_filter_name')
            ->willReturn('Foo-Bar-BazInputFilter')
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('input_filter_name', 'Foo\\Bar\\BazInputFilter')
            ->shouldBeCalled();
        $this->assertNull($listener($this->event->reveal()));
    }
}
