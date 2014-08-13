<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use ZF\Apigility\Admin\Model\ContentNegotiationModel;
use ZF\Configuration\ConfigResource;

class ContentNegotiationTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/zf-apigility-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
    }

    public function tearDown()
    {
        //$this->removeConfigMocks();
    }

    public function createConfigMocks()
    {
        if (!is_dir($this->configPath)) {
            mkdir($this->configPath, 0775, true);
        }

        $contents = "<" . "?php\nreturn array();";
        file_put_contents($this->globalConfigPath, $contents);
    }

    public function removeConfigMocks()
    {
        if (file_exists($this->globalConfigPath)) {
            unlink($this->globalConfigPath);
        }
        if (is_dir($this->configPath)) {
            rmdir($this->configPath);
        }
        if (is_dir(dirname($this->configPath))) {
            rmdir(dirname($this->configPath));
        }
    }

    public function createModelFromConfigArray(array $global)
    {
        $this->configWriter->toFile($this->globalConfigPath, $global);
        $globalConfig = new ConfigResource($global, $this->globalConfigPath, $this->configWriter);
        return new ContentNegotiationModel($globalConfig);
    }

    public function assertContentConfigExists($contentName, array $config)
    {
        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $this->assertArrayHasKey('selectors', $config['zf-content-negotiation']);
        $this->assertArrayHasKey($contentName, $config['zf-content-negotiation']['selectors']);
        $this->assertInternalType('array', $config['zf-content-negotiation']['selectors'][$contentName]);
    }

    public function assertContentConfigEquals(array $expected, $contentName, array $config)
    {
        $this->assertContentConfigExists($contentName, $config);
        $config = $config['zf-content-negotiation']['selectors'][$contentName];
        $this->assertEquals($expected, $config);
    }

    public function assertContentConfigContains(array $expected, $contentName, array $config)
    {
        $this->assertContentConfigExists($contentName, $config);
        $config = $config['zf-content-negotiation']['selectors'][$contentName];
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $config);
            $this->assertEquals($value, $config[$key]);
        }
    }

    public function testCreateContentNegotiation()
    {
        $toCreate = array(
            'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
        );
        $model = $this->createModelFromConfigArray(array());
        $model->create('Json', $toCreate);

        $global = include $this->globalConfigPath;
        $this->assertContentConfigEquals($toCreate, 'Json', $global);
    }

    public function testUpdateContentNegotiation()
    {
        $toCreate = array(
           'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
        );
        $model = $this->createModelFromConfigArray(array());
        $model->create('Json', $toCreate);

        $toUpdate = array(
            'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
            ),
        );
        $model->update('Json', $toUpdate);
        $global = include $this->globalConfigPath;
        $this->assertContentConfigEquals($toUpdate, 'Json', $global);
    }

    public function testRemoveContentNegotiation()
    {
        $toCreate = array(
           'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
        );
        $model = $this->createModelFromConfigArray(array());
        $model->create('Json', $toCreate);

        $model->remove('Json');
        $global = include $this->globalConfigPath;
        $this->assertArrayNotHasKey('Json', $global['zf-content-negotiation']['selectors']);
    }

    public function testFetchAllContentNegotiation()
    {
        $toCreate = array(
            'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
        );
        $model = $this->createModelFromConfigArray(array());
        $model->create('Json', $toCreate);

        $toCreate2 = array(
            'ZF\ContentNegotiation\FooModel' => array(
                'application/foo',
            ),
        );
        $model->create('Foo', $toCreate2);

        $global = include $this->globalConfigPath;
        $this->assertContentConfigContains($toCreate, 'Json', $global);
        $this->assertContentConfigContains($toCreate2, 'Foo', $global);

        $result = $model->fetchAll();
        $this->assertInternalType('array', $result);
        foreach ($result as $value) {
            $this->assertInstanceOf('ZF\Apigility\Admin\Model\ContentNegotiationEntity', $value);
        }
    }

    public function testFetchContentNegotiation()
    {
        $toCreate = array(
            'ZF\ContentNegotiation\JsonModel' => array(
                'application/json',
                'application/*+json',
            ),
        );
        $model = $this->createModelFromConfigArray(array());
        $model->create('Json', $toCreate);

        $content = $model->fetch('Json');
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\ContentNegotiationEntity', $content);
        $arrayCopy = $content->getArrayCopy();
        $this->assertArrayHasKey('content_name', $arrayCopy);
        $this->assertEquals('Json', $arrayCopy['content_name']);
        $this->assertArrayHasKey('selectors', $arrayCopy);
        $this->assertEquals($toCreate, $arrayCopy['selectors']);
    }
}
