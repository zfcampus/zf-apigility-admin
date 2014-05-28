<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\CryptFilterListener;

class CryptFilterListenerTest extends TestCase
{
    public function setUp()
    {
        $this->listener   = new CryptFilterListener();
        $this->event      = new MvcEvent();
        $this->request    = $this->getMock('Zend\Http\Request');
        $this->routeMatch = $this->getMockBuilder('Zend\Mvc\Router\RouteMatch')
            ->disableOriginalConstructor(true)
            ->getMock();
        $this->event->setRequest($this->request);
    }

    protected function initRequestMethod()
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(true));
    }

    protected function initRouteMatch()
    {
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue('ZF\Apigility\Admin\Controller\InputFilter'));
        $this->event->setRouteMatch($this->routeMatch);
    }

    public function testReturnsNullIfRequestIsNotAnHttpRequest()
    {
        $request = $this->getMock('Zend\Stdlib\RequestInterface');
        $this->event->setRequest($request);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRequestMethodIsNotPut()
    {
        $this->request->expects($this->once())
            ->method('isPut')
            ->will($this->returnValue(false));
        $this->initRequestMethod();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchesAreNull()
    {
        $this->initRequestMethod();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfRouteMatchDoesNotContainMatchingController()
    {
        $this->initRequestMethod();
        $this->routeMatch->expects($this->once())
            ->method('getParam')
            ->with($this->equalTo('controller'), $this->equalTo(false))
            ->will($this->returnValue(false));
        $this->event->setRouteMatch($this->routeMatch);
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfNoContentNegotiationParameterDataPresent()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsNullIfParameterDataDoesNotContainFilters()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('ZFContentNegotiationParameterData', array('foo' => 'bar'));
        $this->assertNull($this->listener->onRoute($this->event));
    }

    public function testReturnsTrueIfProcessesParameterData()
    {
        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('ZFContentNegotiationParameterData', array('filters' => array()));
        $this->assertTrue($this->listener->onRoute($this->event));
    }

    public function testUpdatesParameterDataIfAnyCompressionOrEncryptionFiltersDetected()
    {
        $filters = array(
            array(
                'name' => 'Zend\Filter\Encrypt\BlockCipher',
            ),
            array(
                'name' => 'Zend\Filter\Compress\Gz',
            ),
        );

        $this->initRequestMethod();
        $this->initRouteMatch();
        $this->event->setParam('ZFContentNegotiationParameterData', array('filters' => $filters));
        $this->assertTrue($this->listener->onRoute($this->event));
        $data = $this->event->getParam('ZFContentNegotiationParameterData');
        $filters = $data['filters'];

        foreach ($filters as $filter) {
            $this->assertArrayHasKey('name', $filter);
            $this->assertArrayHasKey('options', $filter);
            $this->assertArrayHasKey('adapter', $filter['options']);

            switch ($filter['name']) {
                case 'Zend\Filter\Compress':
                    $this->assertEquals('Gz', $filter['options']['adapter']);
                    break;
                case 'Zend\Filter\Encrypt':
                    $this->assertEquals('BlockCipher', $filter['options']['adapter']);
                    break;
                default:
                    $this->fail('Unrecognized filter: ' . $filter['name']);
            }
        }
    }
}
