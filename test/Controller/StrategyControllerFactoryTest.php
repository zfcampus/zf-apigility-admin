<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;

class StrategyControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class)->reveal();
    }

    public function testInvokableFactoryReturnsStrategyController()
    {
        $factory = new StrategyControllerFactory();

        $controller = $factory($this->container, StrategyController::class);

        $this->assertInstanceOf(StrategyController::class, $controller);
        $this->assertSame($this->container, $controller->getServiceLocator());
    }

    public function testLegacyFactoryReturnsStrategyController()
    {
        $factory = new StrategyControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);

        $controllers->getServiceLocator()->willReturn($this->container);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(StrategyController::class, $controller);
        $this->assertSame($this->container, $controller->getServiceLocator());
    }
}
