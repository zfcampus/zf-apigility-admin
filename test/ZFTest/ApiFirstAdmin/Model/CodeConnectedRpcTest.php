<?php

namespace ZFTest\ApiFirstAdmin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApiFirstAdmin\Model\CodeConnectedRpc;
use ReflectionClass;
use ZF\Configuration\ResourceFactory;
use Zend\Config\Writer\PhpArray;

class CodeConnecedRpcTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'ZFTest\ApiFirstAdmin\Model\TestAsset\FooConf' => new TestAsset\FooConf\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->module = 'ZFTest\ApiFirstAdmin\Model\TestAsset\FooConf';
        $this->resource = new ResourceFactory($this->moduleManager, new PhpArray());
        $this->codeRpc = new CodeConnectedRpc($this->module, $this->resource->factory('ZFTest\ApiFirstAdmin\Model\TestAsset\FooConf'));
    } 

    public function testCreateControllerRpc()
    {
        $serviceName = 'Bar';
        //$path        = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');
        $path = '.';
        $module      = $this->module;

        mkdir($path);
        mkdir("$path/module");
        mkdir("$path/module/$module");
        mkdir("$path/module/$module/src");
        mkdir("$path/module/$module/src/$module");

        $this->assertTrue($this->codeRpc->createController($serviceName, $path));

        $fileName  = sprintf("%s/module/%s/src/%s/Controller/%sController.php", $path, $module, $module, $serviceName);
        $className = sprintf("%s\Controller\%sController", $module, $serviceName);
        require_once $fileName;
        $controllerClass = new ReflectionClass($className);
        
        $this->assertTrue($controllerClass->isSubclassOf('Zend\Mvc\Controller\AbstractActionController'));
        $this->assertTrue($controllerClass->hasMethod($serviceName . 'Action'));

        //$this->removeDir($path);
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    protected function removeDir($dir)
    {
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    } 

}
