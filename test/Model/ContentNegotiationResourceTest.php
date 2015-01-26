<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Config\Writer\PhpArray as ConfigWriter;
use ZF\Apigility\Admin\Model\ContentNegotiationModel;
use ZF\Apigility\Admin\Model\ContentNegotiationResource;
use ZF\Configuration\ConfigResource;

class ContentNegotiationResourceTest extends TestCase
{
    public function setUp()
    {
        $this->configPath       = sys_get_temp_dir() . '/zf-apigility-admin/config';
        $this->globalConfigPath = $this->configPath . '/global.php';
        $this->removeConfigMocks();
        $this->createConfigMocks();
        $this->configWriter     = new ConfigWriter();
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

    public function createResourceFromConfigArray(array $global)
    {
        return new ContentNegotiationResource($this->createModelFromConfigArray($global));
    }


    public function testCreateShouldAcceptContentNameAndReturnNewEntity()
    {
        $resource = $this->createResourceFromConfigArray(array());
        $data = (object) array('content_name' => 'Test');
        $entity = $resource->create($data);
        $this->assertInstanceOf('ZF\Apigility\Admin\Model\ContentNegotiationEntity', $entity);
        $this->assertEquals('Test', $entity->name);
    }
}
