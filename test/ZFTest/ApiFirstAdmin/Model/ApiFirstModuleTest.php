<?php

namespace ZFTest\ApiFirstAdmin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApiFirstAdmin\Model\ApiFirstModule;

class ApiFirstModuleTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Foo' => new TestAsset\Foo\Module(),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar' => new TestAsset\Bar\Module(),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Baz' => new TestAsset\Baz\Module(),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bat' => new TestAsset\Bat\Module(),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bob' => new TestAsset\Bob\Module(),
        );
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Foo\Controller\Foo' => null, // this should never be returned
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Bar' => null,
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Baz' => null,
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bat\Controller\Bat' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Foo\Controller\Act' => null, // this should never be returned
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Act' => null,
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Do'  => null,
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bat\Controller\Act' => null, // this should never be returned
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bob\Controller\Do'  => null,
        );

        $this->model         = new ApiFirstModule($this->moduleManager, $restConfig, $rpcConfig);
    }

    public function testEnabledModulesOnlyReturnsThoseThatImplementApiFirstModuleInterface()
    {
        $expected = array(
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar',
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Baz',
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bob',
        );
        $this->assertEquals($expected, $this->model->getEnabledModules());
    }

    public function invalidModules()
    {
        return array(
            array('ZFTest\ApiFirstAdmin\Model\TestAsset\Foo'),
            array('ZFTest\ApiFirstAdmin\Model\TestAsset\Bat'),
        );
    }

    /**
     * @dataProvider invalidModules
     */
    public function testNullIsReturnedWhenGettingEndpointsForNonApiFirstModules($module)
    {
        $this->assertNull($this->model->getEndpointsByModule($module));
    }

    public function testEmptyArraysAreReturnedWhenGettingEndpointsForApiFirstModulesWithNoEndpoints()
    {
        $expected = array('rest' => array(), 'rpc' => array());
        $this->assertEquals($expected, $this->model->getEndpointsByModule('ZFTest\ApiFirstAdmin\Model\TestAsset\Baz'));
    }

    public function testRestAndRpcControllersAreDiscoveredWhenGettingEndpointsForApiFirstModules()
    {
        $expected = array(
            'rest' => array(
                'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Bar',
                'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Baz',
            ),
            'rpc' => array(
                'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Act',
                'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Do',
            ),
        );
        $this->assertEquals($expected, $this->model->getEndpointsByModule('ZFTest\ApiFirstAdmin\Model\TestAsset\Bar'));
    }

    public function testCanRetrieveListOfAllApiFirstModulesAndTheirEndpoints()
    {
        $expected = array(
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar' => array(
                'rest' => array(
                    'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Bar',
                    'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Baz',
                ),
                'rpc' => array(
                    'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Act',
                    'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Controller\Do',
                ),
            ),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Baz' => array(
                'rest' => array(),
                'rpc'  => array(),
            ),
            'ZFTest\ApiFirstAdmin\Model\TestAsset\Bob' => array(
                'rest' => array(
                ),
                'rpc' => array(
                    'ZFTest\ApiFirstAdmin\Model\TestAsset\Bob\Controller\Do',
                ),
            ),
        );
        $this->assertEquals($expected, $this->model->getEndpointsSortedByModule());
    }
}
