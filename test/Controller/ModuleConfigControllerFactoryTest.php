<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\AbstractPluginManager;
use ZF\Configuration\ConfigResourceFactory;
use ZF\Configuration\ResourceFactory;

class ModuleConfigControllerFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $this->container = $this->prophesize(ContainerInterface::class);
        $this->container->get(ConfigResourceFactory::class)->willReturn($this->configResourceFactory);
    }

    public function testInvokableFactoryReturnsModuleConfigControllerComposingConfigResourceFactory()
    {
        $factory = new ModuleConfigControllerFactory();

        $controller = $factory($this->container->reveal(), ModuleConfigController::class);

        $this->assertInstanceOf(ModuleConfigController::class, $controller);
        $this->assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }

    public function testLegacyFactoryReturnsModuleConfigControllerComposingConfigResource()
    {
        $factory = new ModuleConfigControllerFactory();
        $controllers = $this->prophesize(AbstractPluginManager::class);
        $controllers->getServiceLocator()->will([$this->container, 'reveal']);

        $controller = $factory->createService($controllers->reveal());

        $this->assertInstanceOf(ModuleConfigController::class, $controller);
        $this->assertAttributeSame($this->configResourceFactory, 'configFactory', $controller);
    }
}
