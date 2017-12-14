<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Model\ModuleVersioningModelFactory;

class VersioningControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(ModuleVersioningModelFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleVersioningModelFactory::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsVersioningController()
    {
        $factory = new VersioningControllerFactory();

        $controller = $factory($this->container->reveal(), VersioningController::class);

        $this->assertInstanceOf(VersioningController::class, $controller);
        $this->assertAttributeSame($this->model, 'modelFactory', $controller);
    }

    public function testLegacyFactoryReturnsVersioningController()
    {
        $factory = new VersioningControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(VersioningController::class, $controller);
        $this->assertAttributeSame($this->model, 'modelFactory', $controller);
    }
}
