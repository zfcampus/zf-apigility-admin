<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit\Framework\TestCase;
use Zend\Config\Writer\WriterInterface;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Configuration\ResourceFactory;

class DocumentationModelTest extends TestCase
{
    protected $actualDocData;

    protected $docModel = null;

    public function setup()
    {
        $this->actualDocData = include __DIR__ . '/TestAsset/module/Doc/config/documentation.config.php';

        $mockModuleUtils = $this->getMockBuilder('ZF\Configuration\ModuleUtils')
            ->disableOriginalConstructor()
            ->getMock();
        $mockModuleUtils
            ->expects($this->any())
            ->method('getModuleConfigPath')
            ->will($this->returnValue(__DIR__ . '/TestAsset/module/Doc/config/module.config.php'));

        $configResourceFactory = new ResourceFactory(
            $mockModuleUtils,
            $this->prophesize(WriterInterface::class)->reveal()
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
}
