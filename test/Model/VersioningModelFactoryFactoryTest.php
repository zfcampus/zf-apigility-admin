<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ConfigResourceFactory;
use ZF\Configuration\ResourceFactory;

class VersioningModelFactoryFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function missingDependencies()
    {
        return [
            'all' => [[
                ConfigResourceFactory::class => false,
                ModulePathSpec::class => false,
            ]],
            'ConfigResourceFactory' => [[
                ConfigResourceFactory::class => false,
                ModulePathSpec::class => true,
            ]],
            'ModulePathSpec' => [[
                ConfigResourceFactory::class => true,
                ModulePathSpec::class => false,
            ]],
        ];
    }

    /**
     * @dataProvider missingDependencies
     */
    public function testFactoryRaisesExceptionWhenMissingDependencies($dependencies)
    {
        $factory = new VersioningModelFactoryFactory();

        foreach ($dependencies as $dependency => $presence) {
            $this->container->has($dependency)->willReturn($presence);
        }

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing one or more dependencies');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleVersioningModelFactory()
    {
        $factory = new VersioningModelFactoryFactory();
        $configResourceFactory = $this->prophesize(ResourceFactory::class)->reveal();
        $pathSpec = $this->prophesize(ModulePathSpec::class)->reveal();

        $this->container->has(ConfigResourceFactory::class)->willReturn(true);
        $this->container->has(ModulePathSpec::class)->willReturn(true);
        $this->container->get(ConfigResourceFactory::class)->willReturn($configResourceFactory);
        $this->container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $versioningFactory = $factory($this->container->reveal());

        $this->assertInstanceOf(VersioningModelFactory::class, $versioningFactory);
        $this->assertAttributeSame($configResourceFactory, 'configFactory', $versioningFactory);
        $this->assertAttributeSame($pathSpec, 'moduleUtils', $versioningFactory);
    }
}
