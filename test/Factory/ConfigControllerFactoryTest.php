<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Apigility\Admin\Controller\ConfigController;
use ZF\Apigility\Admin\Factory\ConfigControllerFactory;
use ZF\Configuration\ConfigResource;

class ConfigControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->configResource = $this->prophesize(ConfigResource::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResource::class)->willReturn($this->configResource);
    }

    public function testInvokableFactoryReturnsConfigControllerComposingConfigResource()
    {
        $factory = new ConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ConfigController::class);

        $this->assertInstanceOf(ConfigController::class, $controller);
        $this->assertAttributeSame($this->configResource, 'config', $controller);
    }

    public function testLegacyFactoryReturnsConfigControllerComposingConfigResource()
    {
        $factory = new ConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ConfigController::class, $controller);
        $this->assertAttributeSame($this->configResource, 'config', $controller);
    }
}
