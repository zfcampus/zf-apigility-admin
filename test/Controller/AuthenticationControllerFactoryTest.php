<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Model\AuthenticationModel;

class AuthenticationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->model = $this->prophesize(AuthenticationModel::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthenticationModel::class)->willReturn($this->model);
    }

    public function testInvokableFactoryReturnsAuthenticationController()
    {
        $factory = new AuthenticationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthenticationController::class);

        $this->assertInstanceOf(AuthenticationController::class, $controller);
        $this->assertAttributeSame($this->model, 'model', $controller);
    }

    public function testLegacyFactoryReturnsAuthenticationController()
    {
        $factory = new AuthenticationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(AuthenticationController::class, $controller);
        $this->assertAttributeSame($this->model, 'model', $controller);
    }
}
