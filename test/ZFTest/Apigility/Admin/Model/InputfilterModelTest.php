<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\InputfilterModel;
use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;
use Zend\Config\Writer\PhpArray;

require_once __DIR__ . '/TestAsset/module/InputFilter/Module.php';

class InputfilterModelTest extends TestCase
{
    public function setUp()
    {
        $modules = array(
            'InputFilter' => new \InputFilter\Module()
        );
        

        $this->moduleManager = $this->getMockBuilder('Zend\ModuleManager\ModuleManager')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        
        $this->moduleManager->expects($this->any())
                            ->method('getLoadedModules')
                            ->will($this->returnValue($modules));

        $this->writer        = new PhpArray();
        $moduleUtils         = new ModuleUtils($this->moduleManager);
        $this->configFactory = new ConfigResourceFactory($moduleUtils, $this->writer);
        $this->model         = new InputfilterModel($this->configFactory);

        $this->basePath      = __DIR__ . '/TestAsset/module/InputFilter/config';
        $this->config        = include $this->basePath . '/module.config.php';

        copy($this->basePath . '/module.config.php', $this->basePath . '/module.config.php.old');
    }

    public function tearDown()
    {
        copy($this->basePath .'/module.config.php.old', $this->basePath . '/module.config.php');
        unlink($this->basePath . '/module.config.php.old');
    }

    public function testFetch()
    {
        $result = $this->model->fetch('InputFilter', 'InputFilter\V1\Rest\Foo\Controller');
        $this->assertTrue(!empty($result));
        $this->assertEquals($this->config['input_filters']['InputFilter\V1\Rest\Foo\Validator']['foo'], $result['foo']);
    }

    public function testAddInputfilter()
    {
        $inputfilter = [
            'bar' => [
                'name' => 'bar',
                'validators' => [
                    [
                        'name' => 'NotEmpty'
                    ]
                ]
            ]
        ];
        $result = $this->model->update('InputFilter', 'InputFilter\V1\Rest\Foo\Controller', $inputfilter);
        $this->assertEquals($inputfilter['bar'], $result['input_filters']['InputFilter\V1\Rest\Foo\Validator']['bar']);
    }

    public function testModuleExists()
    {
        $this->assertTrue($this->model->moduleExists('InputFilter'));
    }

    public function testControllerExists()
    {
        $this->assertTrue($this->model->controllerExists('InputFilter', 'InputFilter\V1\Rest\Foo\Controller'));
    }
}
