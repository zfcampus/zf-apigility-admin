<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Zend\Config\Writer\WriterInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\ContentNegotiationModel;
use ZF\Apigility\Admin\Model\ContentNegotiationModelFactory;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ConfigWriter;

class ContentNegotiationModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->writer = $this->prophesize(WriterInterface::class)->reveal();
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new ContentNegotiationModelFactory();

        $this->container->has('config')->willReturn(false);
        $this->container->get('config')->shouldNotBeCalled();
        $this->container->get(ConfigWriter::class)->shouldNotBeCalled();

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredContentNegotiationModel()
    {
        $factory = new ContentNegotiationModelFactory();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($this->writer);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(ContentNegotiationModel::class, $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $config = $r->getValue($model);

        $this->assertAttributeEquals([], 'config', $config);
        $this->assertAttributeEquals('config/autoload/global.php', 'fileName', $config);
        $this->assertAttributeSame($this->writer, 'writer', $config);
    }
}
