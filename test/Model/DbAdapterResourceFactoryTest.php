<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DbAdapterModel;
use ZF\Apigility\Admin\Model\DbAdapterResource;
use ZF\Apigility\Admin\Model\DbAdapterResourceFactory;

class DbAdapterResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfDbAdapterModelIsNotInContainer()
    {
        $factory = new DbAdapterResourceFactory();
        $this->container->has(DbAdapterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(DbAdapterModel::class . ' service is not present');

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredDbAdapterResource()
    {
        $factory = new DbAdapterResourceFactory();
        $model = $this->prophesize(DbAdapterModel::class)->reveal();

        $this->container->has(DbAdapterModel::class)->willReturn(true);
        $this->container->get(DbAdapterModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(DbAdapterResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
    }
}
