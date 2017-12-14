<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Apigility\Admin\Model\InputFilterModel;
use ZF\Apigility\Admin\Model\RpcServiceModelFactory;
use ZF\Apigility\Admin\Model\RpcServiceResource;
use ZF\Apigility\Admin\Model\RpcServiceResourceFactory;

class RpcServiceResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRpcServiceModelFactoryInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . RpcServiceModelFactory::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . InputFilterModel::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingControllerManagerInContainer()
    {
        $factory = new RpcServiceResourceFactory();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has('ControllerManager')->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ControllerManager dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRpcServiceResource()
    {
        $factory            = new RpcServiceResourceFactory();
        $rpcFactory         = $this->prophesize(RpcServiceModelFactory::class)->reveal();
        $inputFilterModel   = $this->prophesize(InputFilterModel::class)->reveal();
        $controllerManager  = $this->prophesize(ControllerManager::class)->reveal();
        $documentationModel = $this->prophesize(DocumentationModel::class)->reveal();

        $this->container->has(RpcServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);
        $this->container->has('ControllerManager')->willReturn(true);

        $this->container->get(RpcServiceModelFactory::class)->willReturn($rpcFactory);
        $this->container->get(InputFilterModel::class)->willReturn($inputFilterModel);
        $this->container->get('ControllerManager')->willReturn($controllerManager);
        $this->container->get(DocumentationModel::class)->willReturn($documentationModel);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(RpcServiceResource::class, $resource);
        $this->assertAttributeSame($rpcFactory, 'rpcFactory', $resource);
        $this->assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        $this->assertAttributeSame($controllerManager, 'controllerManager', $resource);
        $this->assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
