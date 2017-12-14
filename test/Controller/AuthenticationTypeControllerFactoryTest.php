<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener;

class AuthenticationTypeControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->listener = $this->prophesize(DefaultAuthenticationListener::class)->reveal();
        $this->container->get(DefaultAuthenticationListener::class)->willReturn($this->listener);
    }

    public function testInvokableFactoryReturnsAuthenticationTypeController()
    {
        $factory = new AuthenticationTypeControllerFactory();

        $controller = $factory($this->container->reveal(), AuthenticationTypeController::class);

        $this->assertInstanceOf(AuthenticationTypeController::class, $controller);
        $this->assertAttributeSame($this->listener, 'authListener', $controller);
    }

    public function testLegacyFactoryReturnsAuthenticationTypeController()
    {
        $factory = new AuthenticationTypeControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(AuthenticationTypeController::class, $controller);
        $this->assertAttributeSame($this->listener, 'authListener', $controller);
    }
}
