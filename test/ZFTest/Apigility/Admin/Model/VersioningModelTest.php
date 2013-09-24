<?php

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\VersioningModel;
use Test;
use ZF\Apigility\Admin\Model\ModuleModel;

class VersioningModelTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Version\V1' => new TestAsset\Version\V1\Module(),
        );
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Version\V1\Controller\Version' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'ZFTest\Apigility\Admin\Model\TestAsset\Version\V1\Controller\Version' => null, // this should never be returned
        );

        $this->moduleModel = new ModuleModel($this->moduleManager, $restConfig, $rpcConfig);
        $this->model       = new VersioningModel($this->moduleModel);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('ZFTest\Apigility\Admin\Model\TestAsset\Version\V1', 2);

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/config"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/src"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/Module.php"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/src/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/src/Version/V2/Module.php"));
        $this->assertContains(
            '/V2/Module.php', 
            file_get_contents(__DIR__ . "/TestAsset/Version/V2/Module.php")
        );
        $this->assertContains(
            'namespace ZFTest\Apigility\Admin\Model\TestAsset\Version\V2;', 
            file_get_contents(__DIR__ . "/TestAsset/Version/V2/src/Version/V2/Module.php")
        );
        $this->removeDir(__DIR__ . "/TestAsset/Version/V2");
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
