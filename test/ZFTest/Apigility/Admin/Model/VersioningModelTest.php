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
            'ZFTest\Apigility\Admin\Model\TestAsset\Version' => new TestAsset\Version\Module,
        );
        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $restConfig           = array(
            'ZFTest\Apigility\Admin\Model\TestAsset\Version\V1\Rest' => null, // this should never be returned
        );

        $rpcConfig          = array(
            // controller => empty pairs
            'ZFTest\Apigility\Admin\Model\TestAsset\Version\V1\Rpc' => null, // this should never be returned
        );

        $this->moduleModel = new ModuleModel($this->moduleManager, $restConfig, $rpcConfig);
        $this->model       = new VersioningModel($this->moduleModel);
    }

    public function testGetModuleVersions()
    {
        $versions = $this->model->getModuleVersions('ZFTest\Apigility\Admin\Model\TestAsset\Version');
        $this->assertEquals(array(1), $versions);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('ZFTest\Apigility\Admin\Model\TestAsset\Version', 2);

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/Rpc"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/Version/V2/Rest"));
        
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
