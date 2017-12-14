<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DbConnectedRestServiceModel;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\RestServiceModel;
use ZF\Apigility\Admin\Model\RestServiceModelFactory;
use ZF\Apigility\Admin\Model\RestServiceModelFactoryFactory;
use ZF\Apigility\Doctrine\Admin\Model\DoctrineRestServiceModel;
use ZF\Configuration\ConfigResourceFactory;
use ZF\Configuration\ResourceFactory;

/**
 * @todo Write a test to demonstrate that the DoctrineRestServiceModel::onFetch
 *     method is attached to the shared events; requires a stable
 *     zf-apigility-doctrine module that is forwards compatible with v3
 *     components first.
 */
class RestServiceModelFactoryFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function missingDependencies()
    {
        return [
            'all' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => false,
                ModuleModel::class => false,
                'SharedEventManager' => false,
            ]],
            'ModulePathSpec' => [[
                ModulePathSpec::class => false,
                ConfigResourceFactory::class => true,
                ModuleModel::class => true,
                'SharedEventManager' => true,
            ]],
            'ConfigResourceFactory' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => false,
                ModuleModel::class => true,
                'SharedEventManager' => true,
            ]],
            'ModuleModel' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => true,
                ModuleModel::class => false,
                'SharedEventManager' => true,
            ]],
            'SharedEventManager' => [[
                ModulePathSpec::class => true,
                ConfigResourceFactory::class => true,
                ModuleModel::class => true,
                'SharedEventManager' => false,
            ]],
        ];
    }

    /**
     * @dataProvider missingDependencies
     */
    public function testFactoryRaisesExceptionIfDependenciesAreMissing($dependencies)
    {
        $factory = new RestServiceModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRestServiceModelFactoryAndAttachesSharedListeners()
    {
        $factory               = new RestServiceModelFactoryFactory();
        $pathSpec              = $this->prophesize(ModulePathSpec::class)->reveal();
        $configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $sharedEvents          = $this->prophesize(SharedEventManagerInterface::class);
        $moduleModel           = $this->prophesize(ModuleModel::class)->reveal();
        $moduleManager         = $this->prophesize(ModuleManager::class);

        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->has(ModuleModel::class)->willReturn(true);
        $this->container->has('SharedEventManager')->willReturn(true);

        $this->container->get('SharedEventManager')->will([$sharedEvents, 'reveal']);

        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DbConnectedRestServiceModel::class, 'onFetch']
        )->shouldBeCalled();

        $this->container->get('ModuleManager')->will([$moduleManager, 'reveal']);
        $moduleManager->getLoadedModules(false)->willReturn([]);

        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DoctrineRestServiceModel::class, 'onFetch']
        )->shouldNotBeCalled();

        $this->container->get(ModulePathSpec::class)->willReturn($pathSpec);
        $this->container->get(ConfigResourceFactory::class)->willReturn($configResourceFactory);
        $this->container->get(ModuleModel::class)->willReturn($moduleModel);

        $restFactory = $factory($this->container->reveal());

        $this->assertInstanceOf(RestServiceModelFactory::class, $restFactory);
        $this->assertAttributeSame($pathSpec, 'modules', $restFactory);
        $this->assertAttributeSame($configResourceFactory, 'configFactory', $restFactory);
        $this->assertAttributeSame($sharedEvents->reveal(), 'sharedEventManager', $restFactory);
        $this->assertAttributeSame($moduleModel, 'moduleModel', $restFactory);
    }
}
