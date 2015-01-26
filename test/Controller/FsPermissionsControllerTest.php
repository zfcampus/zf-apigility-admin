<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Controller;

use FilesystemIterator;
use PHPUnit_Framework_TestCase as TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZF\Apigility\Admin\Controller\FsPermissionsController;

class FsPermissionsControllerTest extends TestCase
{
    public function setUp()
    {
        $this->pwd  = getcwd();
        $this->wd   = sys_get_temp_dir() . '/' . 'ag-admin-' . uniqid();
        mkdir($this->wd);
        chdir($this->wd);

        $this->controller     = new FsPermissionsController();
    }

    public function tearDown()
    {
        chdir($this->pwd);
        $this->removeDir($this->wd);
    }

    public function removeDir($directory)
    {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $path) {
            $path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
        }
        rmdir($directory);
    }

    public function testReturnsTrueIfNeitherConfigNorModuleDirectoriesExistButRootIsWritable()
    {
        $result = $this->controller->fsPermissionsAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertTrue($fsPerms);
    }

    public function testReturnsTrueIfConfigAndModuleDirectoriesExistAndAreWritable()
    {
        mkdir($this->wd . '/config/autoload', 0775, true);
        mkdir($this->wd . '/module');

        $result = $this->controller->fsPermissionsAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertTrue($fsPerms);
    }

    public function testReturnsFalseIfNeitherConfigNorModuleDirectoriesExistAndRootIsNotWritable()
    {
        if (!file_exists('/var/log') || !is_dir('/var/log') || is_writable('/var/log')) {
            $this->markTestSkipped('Cannot test, as either /var/log does not exist or is writable');
        }

        chdir('/var/log');

        // Instantiating new controller, as constructor caches getcwd()
        $controller = new FsPermissionsController();
        $result = $controller->fsPermissionsAction();
        $this->assertInstanceOf('ZF\ContentNegotiation\ViewModel', $result);
        $fsPerms = $result->getVariable('fs_perms', null);
        $this->assertNotNull($fsPerms);
        $this->assertFalse($fsPerms);
    }

    public function testReturnsFalseIfConfigAndModuleDirectoriesExistButAreNotWritable()
    {
        $this->markTestSkipped(
            'Unable to determine how to test this case, as requires having a directory not owned by test runner'
        );
    }
}
