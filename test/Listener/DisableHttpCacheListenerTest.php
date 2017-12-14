<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Zend\Http\Header\GenericHeader;
use Zend\Http\Header\GenericMultiHeader;
use Zend\Http\Headers;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\DisableHttpCacheListener;
use ZFTest\Apigility\Admin\RouteAssetsTrait;

class DisableHttpCacheListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event = $this->prophesize(MvcEvent::class);
        $this->routeMatch = $this->prophesize($this->getRouteMatchClass());
        $this->request = $this->prophesize(Request::class);
        $this->response = $this->prophesize(Response::class);
        $this->headers = $this->prophesize(Headers::class);
    }

    public function testListenerDoesNothingIfNoRouteMatchPresent()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->willReturn(null);

        $this->routeMatch->getParam(Argument::any())->shouldNotBeCalled();
        $this->event->getRequest()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRouteMatchNotForAdminApi()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_apigility_admin_api', false)->willReturn(false);
        $this->event->getRequest()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerDoesNothingIfRequestIsNotAGetOrHeadRequest()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_apigility_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(false);
        $this->request->isHead()->willReturn(false);
        $this->event->getResponse()->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerInjectsCacheBustHeadersForGetRequests()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_apigility_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(true);
        $this->request->isHead()->willReturn(false);
        $this->event->getResponse()->will([$this->response, 'reveal']);
        $this->response->getHeaders()->will([$this->headers, 'reveal']);
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Expires') {
                return false;
            }
            if ($header->getFieldValue() !== '0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'no-store, no-cache, must-revalidate') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'post-check=0, pre-check=0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeaderLine('Pragma', 'no-cache')->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testListenerInjectsCacheBustHeadersForHeadRequests()
    {
        $listener = new DisableHttpCacheListener();

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal']);

        $this->routeMatch->getParam('is_apigility_admin_api', false)->willReturn(true);
        $this->event->getRequest()->will([$this->request, 'reveal']);
        $this->request->isGet()->willReturn(false);
        $this->request->isHead()->willReturn(true);
        $this->event->getResponse()->will([$this->response, 'reveal']);
        $this->response->getHeaders()->will([$this->headers, 'reveal']);
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Expires') {
                return false;
            }
            if ($header->getFieldValue() !== '0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'no-store, no-cache, must-revalidate') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeader(Argument::that(function ($header) {
            if (! $header instanceof GenericMultiHeader) {
                return false;
            }
            if ($header->getFieldName() !== 'Cache-Control') {
                return false;
            }
            if ($header->getFieldValue() !== 'post-check=0, pre-check=0') {
                return false;
            }
            return true;
        }))->shouldBeCalled();
        $this->headers->addHeaderLine('Pragma', 'no-cache')->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }
}
