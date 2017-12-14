<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\Model\DoctrineAdapterModel;
use ZF\Apigility\Admin\Model\DoctrineAdapterResource;
use ZF\Apigility\Admin\Model\DoctrineAdapterResourceFactory;

class DoctrineAdapterResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ServiceLocatorInterface::class);
        $this->container->willImplement(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDoctrineAdapterModelIsNotInContainer()
    {
        $factory = new DoctrineAdapterResourceFactory();
        $this->container->has(DoctrineAdapterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(DoctrineAdapterModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDoctrineAdapterResource()
    {
        $factory = new DoctrineAdapterResourceFactory();
        $model = $this->prophesize(DoctrineAdapterModel::class)->reveal();
        $modules = $this->prophesize(ModuleManager::class);

        $this->container->has(DoctrineAdapterModel::class)->willReturn(true);
        $this->container->get(DoctrineAdapterModel::class)->willReturn($model);

        $this->container->get('ModuleManager')->will([$modules, 'reveal']);
        $modules->getLoadedModules(false)->willReturn([
            'FooConf',
            'Version',
        ]);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(DoctrineAdapterResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
        $this->assertAttributeEquals([
            'FooConf',
            'Version',
        ], 'loadedModules', $resource);
        $this->assertAttributeSame($this->container->reveal(), 'serviceLocator', $resource);
    }
}
