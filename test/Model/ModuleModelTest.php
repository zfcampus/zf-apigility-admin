<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\ModuleModel;
use Test;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Configuration\ModuleUtils;

class ModuleModelTest extends TestCase
{
    public $modulePath;

    public function setUp()
    {
        if ($this->modulePath && file_exists($this->modulePath)) {
            $this->removeDir($this->modulePath);
            unset($this->modulePath);
        }

        $modules = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Foa' => new TestAsset\Foa\Module(),
            'ZFTest\Apigility\Admin\Model\TestAsset\Foo' => new TestAsset\Foo\Module(),
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar' => new TestAsset\Bar\Module(),
            'ZFTest\Apigility\Admin\Model\TestAsset\Baz' => new TestAsset\Baz\Module(),
            'ZFTest\Apigility\Admin\Model\TestAsset\Bat' => new TestAsset\Bat\Module(),
            'ZFTest\Apigility\Admin\Model\TestAsset\Bob' => new TestAsset\Bob\Module(),
        );
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Foo\Controller\Foo' => null, // this should never be returned
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Bar' => null,
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Baz' => null,
            'ZFTest\Apigility\Admin\Model\TestAsset\Bat\Controller\Bat' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'ZFTest\Apigility\Admin\Model\TestAsset\Foo\Controller\Act' => null, // this should never be returned
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Act' => null,
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Do'  => null,
            'ZFTest\Apigility\Admin\Model\TestAsset\Bat\Controller\Act' => null, // this should never be returned
            'ZFTest\Apigility\Admin\Model\TestAsset\Bob\Controller\Do'  => null,
        );

        $this->model         = new ModuleModel(
            $this->moduleManager,
            $restConfig,
            $rpcConfig
        );
    }

    public function tearDown()
    {
        if ($this->modulePath && file_exists($this->modulePath)) {
            $this->removeDir($this->modulePath);
            unset($this->modulePath);
        }
    }

    public function testEnabledModulesOnlyReturnsThoseThatImplementApigilityProviderInterface()
    {
        $expected = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar',
            'ZFTest\Apigility\Admin\Model\TestAsset\Baz',
            'ZFTest\Apigility\Admin\Model\TestAsset\Bob',
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
            array('ZFTest\Apigility\Admin\Model\TestAsset\Foo'),
            array('ZFTest\Apigility\Admin\Model\TestAsset\Bat'),
        );
    }

    /**
     * @dataProvider invalidModules
     */
    public function testNullIsReturnedWhenGettingServicesForNonApigilityModules($module)
    {
        $this->assertNull($this->model->getModule($module));
    }

    public function testEmptyArraysAreReturnedWhenGettingServicesForApigilityModulesWithNoServices()
    {
        $module = $this->model->getModule('ZFTest\Apigility\Admin\Model\TestAsset\Baz');
        $this->assertEquals(array(), $module->getRestServices());
        $this->assertEquals(array(), $module->getRpcServices());
    }

    public function testRestAndRpcControllersAreDiscoveredWhenGettingServicesForApigilityModules()
    {
        $expected = array(
            'rest' => array(
                'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Bar',
                'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Baz',
            ),
            'rpc' => array(
                'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Act',
                'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Do',
            ),
        );
        $module = $this->model->getModule('ZFTest\Apigility\Admin\Model\TestAsset\Bar');
        $this->assertEquals($expected['rest'], $module->getRestServices());
        $this->assertEquals($expected['rpc'], $module->getRpcServices());
    }

    /**
     * @group listofservices
     */
    public function testCanRetrieveListOfAllApigilityModulesAndTheirServices()
    {
        /* If this is running from a vendor directory, markTestSkipped() */
        if (preg_match('#[/\\\\]vendor[/\\\\]#', __FILE__)) {
            $this->markTestSkipped('Running from a vendor directory.');
        }

        $expected = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Bar' => array(
                'vendor' => false,
                'rest' => array(
                    'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Bar',
                    'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Baz',
                ),
                'rpc' => array(
                    'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Act',
                    'ZFTest\Apigility\Admin\Model\TestAsset\Bar\Controller\Do',
                ),
            ),
            'ZFTest\Apigility\Admin\Model\TestAsset\Baz' => array(
                'vendor' => false,
                'rest' => array(),
                'rpc'  => array(),
            ),
            'ZFTest\Apigility\Admin\Model\TestAsset\Bob' => array(
                'vendor' => false,
                'rest' => array(
                ),
                'rpc' => array(
                    'ZFTest\Apigility\Admin\Model\TestAsset\Bob\Controller\Do',
                ),
            ),
        );

        $modules = $this->model->getModules();

        $unique  = array();
        foreach ($modules as $module) {
            $name = $module->getNamespace();
            $this->assertArrayHasKey(
                $name,
                $expected,
                sprintf('Failed asserting module "%s" is in list', $name)
            );
            $this->assertNotContains(
                $name,
                $unique,
                sprintf('Failed asserting module "%s" was not previously declared', $name)
            );
            $expectedMetadata = $expected[$name];
            $this->assertSame(
                $expectedMetadata['vendor'],
                $module->isVendor(),
                sprintf(
                    'Failed asserting module "%s" vendor flag matches "%s" (received "%s")',
                    $name,
                    var_export($expectedMetadata['vendor'], 1),
                    var_export($module->isVendor(), 1)
                )
            );
            $this->assertSame(
                $expectedMetadata['rest'],
                $module->getRestServices(),
                sprintf(
                    'Failed asserting module "%s" rest services match expectations; expected [ %s ], received [ %s ]',
                    $name,
                    var_export($expectedMetadata['rest'], 1),
                    var_export($module->getRestServices(), 1)
                )
            );
            $this->assertSame(
                $expectedMetadata['rpc'],
                $module->getRpcServices(),
                sprintf(
                    'Failed asserting module "%s" rpc services match expectations; expected [ %s ], received [ %s ]',
                    $name,
                    var_export($expectedMetadata['rpc'], 1),
                    var_export($module->getRpcServices(), 1)
                )
            );
            $unique[] = $name;
        }
    }

    public function testCreateModule()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V1/Rpc"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/V1/Rest"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
        return true;
    }


    /**
     * @group feature/psr4
     */
    public function testCreateModulePSR4()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath, 'psr-4');

        $this->assertTrue($this->model->createModule($module, $pathSpec));
        $this->assertTrue(file_exists("$modulePath/module/$module"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/V1/Rpc"));
        $this->assertTrue(file_exists("$modulePath/module/$module/src/V1/Rest"));
        $this->assertTrue(file_exists("$modulePath/module/$module/view"));
        $this->assertTrue(file_exists("$modulePath/module/$module/Module.php"));
        $this->assertTrue(file_exists("$modulePath/module/$module/config/module.config.php"));

        $this->removeDir($modulePath);
        return true;
    }

    protected function getPathSpec($modulePath, $spec = 'psr-0')
    {
        return new ModulePathSpec(
            new ModuleUtils($this->moduleManager),
            $spec,
            $modulePath
        );
    }

    /**
     * @depends testCreateModule
     */
    public function testDeleteModule()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array("modules" => array());'
        );

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));
        $config = include $modulePath . '/config/application.config.php';
        $this->assertArrayHasKey('modules', $config, var_export($config, 1));

        // Now try and delete
        $this->assertTrue($this->model->deleteModule($module, $modulePath, false));

        $config = include $modulePath . '/config/application.config.php';
        $this->assertArrayHasKey('modules', $config, var_export($config, 1));
        $this->assertNotContains($module, $config['modules']);
        $this->assertTrue(file_exists(sprintf('%s/module/%s', $modulePath, $module)));

        $this->removeDir($modulePath);
        return true;
    }

    /**
     * @depends testCreateModule
     */
    public function testDeleteModuleRecursively()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . '?php return array("modules" => array());'
        );
        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        // Now try and delete
        $this->assertTrue($this->model->deleteModule($module, $modulePath, true));
        $this->assertFalse(
            file_exists(sprintf('%s/module/%s', $modulePath, $module)),
            shell_exec('tree ' . $modulePath)
        );
    }

    /**
     * @group 22
     */
    public function testReturnFalseWhenTryingToCreateAModuleThatAlreadyExistsInConfiguration()
    {
        $module     = 'Foo';
        $modulePath = sys_get_temp_dir() . "/" . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents(
            "$modulePath/config/application.config.php",
            '<' . "?php return array(\n    'modules' => array(\n        'Foo',\n    )\n);"
        );
        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertFalse($this->model->createModule($module, $pathSpec));
    }

    public function testUpdateExistingApiModule()
    {
        $module = 'ZFTest\Apigility\Admin\Model\TestAsset\Bar';
        $this->assertFalse($this->model->updateModule($module));
    }

    public function testUpdateModule()
    {
        $module = 'ZFTest\Apigility\Admin\Model\TestAsset\Foo';
        $this->assertTrue($this->model->updateModule($module));

        unlink(__DIR__ . '/TestAsset/Foo/Module.php');
        rename(
            __DIR__ . '/TestAsset/Foo/Module.php.old',
            __DIR__ . '/TestAsset/Foo/Module.php'
        );
    }

    public function testUpdateModuleWithOtherInterfaces()
    {
        $module = 'ZFTest\Apigility\Admin\Model\TestAsset\Foa';
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

        $model = new ModuleModel(
            $moduleManager,
            array(),
            array()
        );

        $modules = $model->getModules();
        foreach ($modules as $module) {
            $this->assertTrue($module->isVendor());
        }
    }

    public function testDefaultApiVersionIsSetProperly()
    {
        $modules = array(
            'Test\Bar' => new Test\Bar\Module(),
            'Test\Foo' => new Test\Foo\Module(),
        );
        $moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                              ->disableOriginalConstructor()
                              ->getMock();
        $moduleManager->expects($this->any())
                      ->method('getLoadedModules')
                      ->will($this->returnValue($modules));

        $model = new ModuleModel(
            $moduleManager,
            array(),
            array()
        );

        $modules = $model->getModules();

        $this->assertSame(
            1,
            $modules[0]->getDefaultVersion(),
            'Did not default to version 1 as the default version for unconfigured default version of Test\Bar!'
        );
        $this->assertSame(
            123,
            $modules[1]->getDefaultVersion(),
            'Did not read configured default version 123 for Test\Foo!'
        );
    }

    /**
     * @group 59
     */
    public function testAttemptingToCreateModuleThatAlreadyExistsRaises409Exception()
    {
        $module = 'Foo';
        $this->modulePath = $modulePath = sys_get_temp_dir()
          . "/"
          . uniqid(str_replace('\\', '_', __NAMESPACE__) . '_');

        mkdir("$modulePath/module", 0775, true);
        mkdir("$modulePath/config", 0775, true);
        file_put_contents("$modulePath/config/application.config.php", '<' . '?php return array();');

        $pathSpec = $this->getPathSpec($modulePath);

        $this->assertTrue($this->model->createModule($module, $pathSpec));

        $this->setExpectedException('Exception', 'exists', 409);

        $this->model->createModule($module, $pathSpec);
    }
}
