<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\ModulePathSpecFactory;
use ZF\Configuration\ModuleUtils;

class ModulePathSpecFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfModuleUtilsServiceIsMissing()
    {
        $factory = new ModulePathSpecFactory();

        $this->container->has(ModuleUtils::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage(ModuleUtils::class . ' service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionIfConfiguredModulePathIsNotADirectory()
    {
        $factory = new ModulePathSpecFactory();
        $moduleUtils = $this->prophesize(ModuleUtils::class)->reveal();

        $this->container->has(ModuleUtils::class)->willReturn(true);
        $this->container->get(ModuleUtils::class)->willReturn($moduleUtils);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'zf-apigility-admin' => [
                'module_path' => __FILE__,
            ],
        ]);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('Invalid module path');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModulePathSpec()
    {
        $factory = new ModulePathSpecFactory();
        $moduleUtils = $this->prophesize(ModuleUtils::class)->reveal();

        $this->container->has(ModuleUtils::class)->willReturn(true);
        $this->container->get(ModuleUtils::class)->willReturn($moduleUtils);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn([
            'zf-apigility-admin' => [
                'module_path' => realpath(__DIR__),
                'path_spec' => 'psr-4',
            ],
        ]);

        $pathSpec = $factory($this->container->reveal());

        $this->assertInstanceOf(ModulePathSpec::class, $pathSpec);
        $this->assertAttributeSame($moduleUtils, 'modules', $pathSpec);
        $this->assertAttributeEquals(realpath(__DIR__), 'applicationPath', $pathSpec);
        $this->assertAttributeEquals('%modulePath%/src', 'moduleSourcePathSpec', $pathSpec);
    }
}
