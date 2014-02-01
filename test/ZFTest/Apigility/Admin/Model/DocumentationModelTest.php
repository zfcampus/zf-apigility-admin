<?php

namespace ZFTest\Apigility\Admin\Model;

use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Configuration\ModuleUtils;
use ZF\Configuration\ResourceFactory;
use ZFTest\Configuration\TestAsset\ConfigWriter;

class DocumentationModelTest extends \PHPUnit_Framework_TestCase
{
    protected $actualDocData;

    /** @var DocumentationModel */
    protected $docModel = null;

    public function setup()
    {
        $this->actualDocData = include __DIR__ . '/TestAsset/module/Doc/config/documentation.config.php';

        $mockModuleUtils = $this->getMock('ZF\Configuration\ModuleUtils', ['getModuleConfigPath'], [], '', false);
        $mockModuleUtils->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module/Doc/config/module.config.php'));

        $configResourceFactory = new ResourceFactory(
            $mockModuleUtils,
            new ConfigWriter()
        );
        $this->docModel = new DocumentationModel($configResourceFactory, $mockModuleUtils);
    }

    public function testFetchRestDocumentation()
    {

        $this->assertEquals(
            $this->actualDocData['Doc\\V1\\Rest\\FooBar\\Controller'],
            $this->docModel->fetchDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller')
        );
    }

    /*
    public function testStoreRestDocumentation() {}
    public function testFetchRpcDocumentation() {}
    public function testStoreRpcDocumentation() {}
    */
}
