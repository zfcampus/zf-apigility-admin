<?php

namespace ZFTest\ApiFirstAdmin\Model;

use FooConf;
use PHPUnit_Framework_TestCase as TestCase;
use ReflectionClass;
use ZF\ApiFirstAdmin\Model\CodeConnectedRpc;
use ZF\Configuration\ResourceFactory;
use ZF\Configuration\ModuleUtils;
use Zend\Config\Writer\PhpArray;

require_once __DIR__ . '/TestAsset/module/FooConf/Module.php';

class CodeConnecedRpcTest extends TestCase
{
    public function setUp()
    {
        $this->module = 'FooConf';
        $srcPath      = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        if (is_dir($srcPath)) {
            $this->removeDir($srcPath);
        }

        $modules = array(
            'FooConf' => new FooConf\Module()
        );

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->modules  = new ModuleUtils($this->moduleManager);
        $this->resource = new ResourceFactory($this->modules, new PhpArray());
        $this->codeRpc  = new CodeConnectedRpc($this->module, $this->modules, $this->resource->factory('FooConf'));
    } 

    public function tearDown()
    {
        $srcPath = sprintf('%s/TestAsset/module/%s/src', __DIR__, $this->module);
        if (is_dir($srcPath)) {
            $this->removeDir($srcPath);
        }
    }

    public function testCreateControllerRpc()
    {
        $serviceName = 'Bar';
        $moduleSrcPath = sprintf('%s/TestAsset/module/%s/src/%s', __DIR__, $this->module, $this->module);
        if (!is_dir($moduleSrcPath)) {
            mkdir($moduleSrcPath, 0777, true);
        }

        $this->assertTrue($this->codeRpc->createController($serviceName));

        $fileName  = sprintf("%s/TestAsset/module/%s/src/%s/Controller/%sController.php", __DIR__, $this->module, $this->module, $serviceName);
        $className = sprintf("%s\Controller\%sController", $this->module, $serviceName);
        require_once $fileName;
        $controllerClass = new ReflectionClass($className);
        
        $this->assertTrue($controllerClass->isSubclassOf('Zend\Mvc\Controller\AbstractActionController'));
        $this->assertTrue($controllerClass->hasMethod($serviceName . 'Action'));
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
