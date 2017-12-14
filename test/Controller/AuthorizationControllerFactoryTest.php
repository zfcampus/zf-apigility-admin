<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Model\AuthorizationModelFactory;

class AuthorizationControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->authFactory = $this->prophesize(AuthorizationModelFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(AuthorizationModelFactory::class)->willReturn($this->authFactory);
    }

    public function testInvokableFactoryReturnsAuthorizationController()
    {
        $factory = new AuthorizationControllerFactory();

        $controller = $factory($this->container->reveal(), AuthorizationController::class);

        $this->assertInstanceOf(AuthorizationController::class, $controller);
        $this->assertAttributeSame($this->authFactory, 'factory', $controller);
    }

    public function testLegacyFactoryReturnsAuthorizationController()
    {
        $factory = new AuthorizationControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(AuthorizationController::class, $controller);
        $this->assertAttributeSame($this->authFactory, 'factory', $controller);
    }
}
