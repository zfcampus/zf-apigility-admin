<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZFTest\ApiFirstAdmin;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Request;
use ZF\ApiFirstAdmin\Controller\ModuleController;
use ZF\ApiFirstAdmin\Model\ApiFirstModule;
use Zend\ModuleManager\ModuleManager;

class ModuleControllerTest extends TestCase
{
    public function setUp()
    {
        $this->moduleManager  = new ModuleManager(array());
        $this->moduleResource = new ApiFirstModule($this->moduleManager, array(), array());
        $this->controller     = new ModuleController($this->moduleResource);
    }

    public function invalidRequestMethods()
    {
        return array(
            array('get'),
            array('patch'),
            array('put'),
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
        $result = $this->controller->processAction();
        $this->assertInstanceOf('ZF\ApiProblem\View\ApiProblemModel', $result);
        $apiProblem = $result->getApiProblem();
        $this->assertEquals(405, $apiProblem->http_status);
    }

    public function testProcessPostRequest()
    {
        $currentDir = getcwd();
        $tmpDir     = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');
        
        mkdir($tmpDir);
        mkdir("$tmpDir/module");
        mkdir("$tmpDir/config");
        file_put_contents("$tmpDir/config/application.config.php",'<?php return array(\'modules\'=>array());');
        chdir($tmpDir);

        $request = new Request();
        $request->setMethod('post');
        $request->setContent(json_encode(array(
            'module' => 'Foo'
        )));
        $this->controller->setRequest($request);
        $result = $this->controller->processAction();
       
        $this->assertTrue(is_array($result));
        $this->assertEquals($result['module'], 'Foo');

        $this->removeDir($tmpDir);
        chdir($currentDir);
    }
    
    protected function removeDir($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->removeDir("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    } 
}
