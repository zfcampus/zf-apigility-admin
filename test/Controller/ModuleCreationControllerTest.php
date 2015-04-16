<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use Zend\ModuleManager\ModuleManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Controller\ModuleCreationController;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Configuration\ModuleUtils;
use ZF\ContentNegotiation\ParameterDataContainer;

class ModuleCreationControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager(array());
        $this->moduleResource = new ModuleModel(
            $this->moduleManager,
            array(),
            array()
        );
        $this->controller     = new ModuleCreationController($this->moduleResource);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('get'),
            array('patch'),
            array('post'),
            array('delete'),
        );
    }

    /**
     * @dataProvider invalidRequestMethods
     */
    public function testProcessWithInvalidRequestMethodReturnsApiProblemResponse($method)
    {
        $request = new Request();
        $request->setMethod($method);
        $this->controller->setRequest($request);
        $result = $this->controller->apiEnableAction();
        $this->assertInstanceOf('ZF\ApiProblem\ApiProblemResponse', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->status);
    }

    public function testProcessPutRequest()
    {
        $currentDir = getcwd();
        $tmpDir     = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');

        mkdir($tmpDir);
        mkdir("$tmpDir/module/Foo", 0775, true);
        mkdir("$tmpDir/config");
        file_put_contents(
            "$tmpDir/config/application.config.php",
            '<' . '?php return array(\'modules\'=>array(\'Foo\'));'
        );
        file_put_contents("$tmpDir/module/Foo/Module.php", "<" . "?php\n\nnamespace Foo;\n\nclass Module\n{\n}");
        chdir($tmpDir);

        require 'module/Foo/Module.php';

        $moduleManager  = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                               ->disableOriginalConstructor()
                               ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue(array('Foo' => new \Foo\Module)));

        $moduleResource = new ModuleModel(
            $moduleManager,
            array(),
            array()
        );
        $controller     = new ModuleCreationController($moduleResource);

        $request = new Request();
        $request->setMethod('put');
        $request->getHeaders()->addHeaderLine('Accept', 'application/json');
        $request->getHeaders()->addHeaderLine('Content-Type', 'application/json');

        $parameters = new ParameterDataContainer();
        $parameters->setBodyParam('module', 'Foo');
        $event = new MvcEvent();
        $event->setParam('ZFContentNegotiationParameterData', $parameters);

        $plugins = new PluginManager();
        $plugins->setInvokableClass('bodyParam', 'ZF\ContentNegotiation\ControllerPlugin\BodyParam');

        $controller->setRequest($request);
        $controller->setEvent($event);
        $controller->setPluginManager($plugins);

        $result = $controller->apiEnableAction();

        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $payload = $result->getVariable('payload');
        $this->assertInstanceOf('ZF\Hal\Entity', $payload);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\ModuleEntity', $payload->entity);

        $metadata = $payload->entity;
        $this->assertEquals('Foo', $metadata->getName());

        $this->removeDir($tmpDir);
        chdir($currentDir);
    }

    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
        }
        return @rmdir($dir);
    }
}
