<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Model;

class DashboardControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->authenticationModel = $this->prophesize(Model\AuthenticationModel::class)->reveal();
        $this->contentNegotiationModel = $this->prophesize(Model\ContentNegotiationModel::class)->reveal();
        $this->dbAdapterModel = $this->prophesize(Model\DbAdapterModel::class)->reveal();
        $this->moduleModel = $this->prophesize(Model\ModuleModel::class)->reveal();
        $this->restServiceModelFactory = $this->prophesize(Model\RestServiceModelFactory::class)->reveal();
        $this->rpcServiceModelFactory = $this->prophesize(Model\RpcServiceModelFactory::class)->reveal();

        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(Model\AuthenticationModel::class)->willReturn($this->authenticationModel);
        $this->container->get(Model\ContentNegotiationModel::class)->willReturn($this->contentNegotiationModel);
        $this->container->get(Model\DbAdapterModel::class)->willReturn($this->dbAdapterModel);
        $this->container->get(Model\ModuleModel::class)->willReturn($this->moduleModel);
        $this->container->get(Model\RestServiceModelFactory::class)->willReturn($this->restServiceModelFactory);
        $this->container->get(Model\RpcServiceModelFactory::class)->willReturn($this->rpcServiceModelFactory);
    }

    public function testInvokableFactoryReturnsDashboardController()
    {
        $factory = new DashboardControllerFactory();

        $controller = $factory($this->container->reveal(), DashboardController::class);

        $this->assertInstanceOf(DashboardController::class, $controller);
        $this->assertAttributeSame($this->authenticationModel, 'authentication', $controller);
        $this->assertAttributeSame($this->contentNegotiationModel, 'contentNegotiation', $controller);
        $this->assertAttributeSame($this->dbAdapterModel, 'dbAdapters', $controller);
        $this->assertAttributeSame($this->moduleModel, 'modules', $controller);
        $this->assertAttributeSame($this->restServiceModelFactory, 'restServicesFactory', $controller);
        $this->assertAttributeSame($this->rpcServiceModelFactory, 'rpcServicesFactory', $controller);
    }

    public function testLegacyFactoryReturnsDashboardController()
    {
        $factory = new DashboardControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(DashboardController::class, $controller);
        $this->assertAttributeSame($this->authenticationModel, 'authentication', $controller);
        $this->assertAttributeSame($this->contentNegotiationModel, 'contentNegotiation', $controller);
        $this->assertAttributeSame($this->dbAdapterModel, 'dbAdapters', $controller);
        $this->assertAttributeSame($this->moduleModel, 'modules', $controller);
        $this->assertAttributeSame($this->restServiceModelFactory, 'restServicesFactory', $controller);
        $this->assertAttributeSame($this->rpcServiceModelFactory, 'rpcServicesFactory', $controller);
    }
}
