<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZFTest\ApiFirstAdmin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use ZF\ApiFirstAdmin\Controller\SourceController;
use ZF\ApiFirstAdmin\Model\ModuleModel;
use ZF\ContentNegotiation\ParameterDataContainer;
use ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Module as BarModule;

class SourceControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager(array());
        $this->moduleResource = new ModuleModel($this->moduleManager, array(), array());
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
        $this->assertEquals(405, $apiProblem->http_status);
    }

    public function testProcessGetRequest()
    {
        $moduleManager  = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(array('ZFTest\ApiFirstAdmin\Model\TestAsset\Bar' => new BarModule)));

        $moduleResource = new ModuleModel($moduleManager, array(), array());
        $controller     = new SourceController($moduleResource);

        $request = new Request();
        $request->setMethod('get');
        $request->getQuery()->module = 'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar';
        $request->getQuery()->class = 'ZFTest\ApiFirstAdmin\Model\TestAsset\Bar\Module';

        $controller->setRequest($request);
        $result = $controller->sourceAction();
        
        $this->assertTrue($result->getVariable('source') != '');
        $this->assertTrue($result->getVariable('file') != '');
        $this->assertEquals($result->getVariable('module'), $request->getQuery()->module);
        $this->assertEquals($result->getVariable('class'), $request->getQuery()->class);        
    }

}
