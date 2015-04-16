<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\ModuleManager\ModuleManager;
use ZF\Apigility\Admin\Controller\SourceController;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Configuration\ModuleUtils;
use ZFTest\Apigility\Admin\Model\TestAsset\Bar\Module as BarModule;

class SourceControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager(array());
        $this->moduleResource = new ModuleModel(
            $this->moduleManager,
            array(),
            array()
        );
        $this->controller     = new SourceController($this->moduleResource);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('put'),
            array('patch'),
            array('post'),
            array('delete'),
        );
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemModel($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->sourceAction();
        $this->assertInstanceOf('ZF\ApiProblem\View\ApiProblemModel', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }

    public function testProcessGetRequest()
    {
        $moduleManager  = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(array('ZFTest\Apigility\Admin\Model\TestAsset\Bar' => new BarModule)));

        $moduleResource = new ModuleModel($moduleManager, array(), array());
        $controller     = new SourceController($moduleResource);

        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->module = 'ZFTest\Apigility\Admin\Model\TestAsset\Bar';
        $request->getQuery()->class = 'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Module';

        $controller->setRequest($request);
        $result = $controller->sourceAction();

        $this->assertTrue($result->getVariable('source') != '');
        $this->assertTrue($result->getVariable('file') != '');
        $this->assertEquals($result->getVariable('module'), $request->getQuery()->module);
        $this->assertEquals($result->getVariable('class'), $request->getQuery()->class);
    }
}
