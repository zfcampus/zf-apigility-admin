<?php

namespace ZFTest\Apigility\Admin\Model;

use Zend\ModuleManager\ModuleManager;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Configuration\ModuleUtils;
use ZF\Configuration\ResourceFactory;
use ZFTest\Configuration\TestAsset\ConfigWriter;

class DocumentationModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var DocumentationModel */
    protected $docModel = null;

    public function setup()
    {
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
            'per rest controller description',
            $this->docModel->fetchRestDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller')
        );

        $this->assertEquals(
            'General in rest collection',
            $this->docModel->fetchRestDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller', 'collection')
        );

        $this->assertEquals(
            'General description for GET',
            $this->docModel->fetchRestDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller', 'entity', 'GET')
        );

        $this->assertEquals(
            'Request for POST doc in collection',
            $this->docModel->fetchRestDocumentation('Doc', 'Doc\\V1\\Rest\\FooBar\\Controller', 'collection', 'POST', 'request')
        );
    }

    /*
    public function testStoreRestDocumentation() {}
    public function testFetchRpcDocumentation() {}
    public function testStoreRpcDocumentation() {}
    */
}
 