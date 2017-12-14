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
use ZF\Apigility\Admin\Model\DoctrineAdapterModel;
use ZF\Apigility\Admin\Model\DoctrineAdapterModelFactory;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ConfigWriter;

class DoctrineAdapterModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfConfigServiceIsMissing()
    {
        $factory = new DoctrineAdapterModelFactory();

        $this->container->has('config')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('config service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsDoctrineAdapterModelComposingConfigResources()
    {
        $factory = new DoctrineAdapterModelFactory();
        $writer  = $this->prophesize(WriterInterface::class)->reveal();

        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([]);
        $this->container->get(ConfigWriter::class)->willReturn($writer);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(DoctrineAdapterModel::class, $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'globalConfig', $model);
        $this->assertAttributeInstanceOf(ConfigResource::class, 'localConfig', $model);

        $r = new ReflectionProperty($model, 'globalConfig');
        $r->setAccessible(true);
        $globalConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $globalConfig);
        $this->assertAttributeEquals('config/autoload/doctrine.global.php', 'fileName', $globalConfig);

        $r = new ReflectionProperty($model, 'localConfig');
        $r->setAccessible(true);
        $localConfig = $r->getValue($model);

        $this->assertAttributeSame($writer, 'writer', $localConfig);
        $this->assertAttributeEquals('config/autoload/doctrine.local.php', 'fileName', $localConfig);
    }
}
