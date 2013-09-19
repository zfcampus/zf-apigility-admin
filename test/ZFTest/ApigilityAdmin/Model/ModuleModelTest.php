<?php

namespace ZFTest\ApigilityAdmin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\ApigilityAdmin\Model\ModuleModel;
use Test;

class ModuleModelTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'ZFTest\ApigilityAdmin\Model\TestAsset\Foa' => new TestAsset\Foa\Module(),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Foo' => new TestAsset\Foo\Module(),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar' => new TestAsset\Bar\Module(),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Baz' => new TestAsset\Baz\Module(),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bat' => new TestAsset\Bat\Module(),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bob' => new TestAsset\Bob\Module(),
        );
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'ZFTest\ApigilityAdmin\Model\TestAsset\Foo\Controller\Foo' => null, // this should never be returned
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Bar' => null,
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Baz' => null,
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bat\Controller\Bat' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'ZFTest\ApigilityAdmin\Model\TestAsset\Foo\Controller\Act' => null, // this should never be returned
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Act' => null,
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Do'  => null,
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bat\Controller\Act' => null, // this should never be returned
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bob\Controller\Do'  => null,
        );

        $this->model         = new ModuleModel($this->moduleManager, $restConfig, $rpcConfig);
    }

    public function testEnabledModulesOnlyReturnsThoseThatImplementApiFirstModuleInterface()
    {
        $expected = array(
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar',
            'ZFTest\ApigilityAdmin\Model\TestAsset\Baz',
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bob',
        );

        $modules = $this->model->getModules();

        // make sure we have the same number of modules
        $this->assertEquals(count($expected), count($modules));

        // Test that each module name exists in the expected list
        $moduleNames = array();
        foreach ($modules as $module) {
            $this->assertContains($module->getNamespace(), $expected);
            $moduleNames[] = $module->getNamespace();
        }

        // Test that we have all unique module names
        $test = array_unique($moduleNames);
        $this->assertEquals($moduleNames, $test);
    }

    public function invalidModules()
    {
        return array(
            array('ZFTest\ApigilityAdmin\Model\TestAsset\Foo'),
            array('ZFTest\ApigilityAdmin\Model\TestAsset\Bat'),
        );
    }

    /**
     * @dataProvider invalidModules
     */
    public function testNullIsReturnedWhenGettingServicesForNonApiFirstModules($module)
    {
        $this->assertNull($this->model->getModule($module));
    }

    public function testEmptyArraysAreReturnedWhenGettingServicesForApiFirstModulesWithNoServices()
    {
        $module = $this->model->getModule('ZFTest\ApigilityAdmin\Model\TestAsset\Baz');
        $this->assertEquals(array(), $module->getRestServices());
        $this->assertEquals(array(), $module->getRpcServices());
    }

    public function testRestAndRpcControllersAreDiscoveredWhenGettingServicesForApiFirstModules()
    {
        $expected = array(
            'rest' => array(
                'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Bar',
                'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Baz',
            ),
            'rpc' => array(
                'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Act',
                'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Do',
            ),
        );
        $module = $this->model->getModule('ZFTest\ApigilityAdmin\Model\TestAsset\Bar');
        $this->assertEquals($expected['rest'], $module->getRestServices());
        $this->assertEquals($expected['rpc'], $module->getRpcServices());
    }

    public function testCanRetrieveListOfAllApiFirstModulesAndTheirServices()
    {
        $expected = array(
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bar' => array(
                'vendor' => false,
                'rest' => array(
                    'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Bar',
                    'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Baz',
                ),
                'rpc' => array(
                    'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Act',
                    'ZFTest\ApigilityAdmin\Model\TestAsset\Bar\Controller\Do',
                ),
            ),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Baz' => array(
                'vendor' => false,
                'rest' => array(),
                'rpc'  => array(),
            ),
            'ZFTest\ApigilityAdmin\Model\TestAsset\Bob' => array(
                'vendor' => false,
                'rest' => array(
                ),
                'rpc' => array(
                    'ZFTest\ApigilityAdmin\Model\TestAsset\Bob\Controller\Do',
                ),
            ),
        );

        $modules = $this->model->getModules();

        $unique  = array();
        foreach ($modules as $module) {
            $name = $module->getNamespace();
            $this->assertArrayHasKey($name, $expected);
            $this->assertNotContains($name, $unique);
            $expectedMetadata = $expected[$name];
            $this->assertSame($expectedMetadata['vendor'], $module->isVendor());
            $this->assertSame($expectedMetadata['rest'], $module->getRestServices());
            $this->assertSame($expectedMetadata['rpc'], $module->getRpcServices());
            $unique[] = $name;
        }
    }

    public function testCreateModule()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(__NAMESPACE__ . '_');

        mkdir($modulePath);
        mkdir("$modulePath/module");
        mkdir("$modulePath/config");
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $this->assertTrue($this->model->createModule($module, $modulePath));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
    }

    public function testUpdateExistingApiModule()
    {
        $module = 'ZFTest\ApigilityAdmin\Model\TestAsset\Bar';
        $this->assertFalse($this->model->updateModule($module));
    }

    public function testUpdateModule()
    {
        $module = 'ZFTest\ApigilityAdmin\Model\TestAsset\Foo';
        $this->assertTrue($this->model->updateModule($module));

        unlink(__DIR__ . '/TestAsset/Foo/Module.php');
        rename(
            __DIR__ . '/TestAsset/Foo/Module.php.old',
            __DIR__ . '/TestAsset/Foo/Module.php'
        );
    }

    public function testUpdateModuleWithOtherInterfaces()
    {
        $module = 'ZFTest\ApigilityAdmin\Model\TestAsset\Foa';
        $this->assertTrue($this->model->updateModule($module));

        unlink(__DIR__ . '/TestAsset/Foa/Module.php');
        rename(
            __DIR__ . '/TestAsset/Foa/Module.php.old',
            __DIR__ . '/TestAsset/Foa/Module.php'
        );
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

    public function testVendorModulesAreMarkedAccordingly()
    {
        $modules = array(
            'Test\Foo' => new Test\Foo\Module(),
            'Test\Bar' => new Test\Foo\Module(),
        );
        $moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue($modules));

        $model = new ModuleModel($moduleManager, array(), array());

        $modules = $model->getModules();
        foreach ($modules as $module) {
            $this->assertTrue($module->isVendor());
        }
    }
}
