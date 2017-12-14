<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Model\ModuleModel;

class ModuleCreationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(ModuleModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ModuleModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsModuleCreationController()
    {
        $factory = new ModuleCreationControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleCreationController::class);

        $this->assertInstanceOf(ModuleCreationController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }

    public function testLegacyFactoryReturnsModuleCreationController()
    {
        $factory = new ModuleCreationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ModuleCreationController::class, $controller);
        $this->assertAttributeSame($this->model, 'moduleModel', $controller);
    }
}
