<?php

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\VersioningModel;
use Test;

class VersioningModelTest extends TestCase
{
    public function setUp()
    {
        $this->model = new VersioningModel();
    }

    public function testGetModuleVersions()
    {
        $versions = $this->model->getModuleVersions('Version', __DIR__ . '/TestAsset');
        $this->assertEquals(array(1), $versions);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset');

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest"));
        
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
        unlink(__DIR__ . "/TestAsset/module/Version/config/module.config.php");
        rename(
            __DIR__ . "/TestAsset/module/Version/config/module.config.php.V1.old",
            __DIR__ . "/TestAsset/module/Version/config/module.config.php"
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
}
