<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ModuleManager\ModuleManager;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Factory\ModuleModelFactory;
use ZF\Apigility\Admin\Model\ModuleModel;

class ModuleModelFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionForMissingModuleManagerInContainer()
    {
        $factory = new ModuleModelFactory();

        $this->container->has('ModuleManager')->willReturn(false);

        $this->setExpectedException(ServiceNotCreatedException::class, 'ModuleManager service is not present');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredModuleModel()
    {
        $factory = new ModuleModelFactory();
        $config  = [
            'zf-rest' => ['rest configuration' => true],
            'zf-rpc'  => ['rpc configuration' => true],
        ];
        $moduleManager = $this->prophesize(ModuleManager::class)->reveal();

        $this->container->has('ModuleManager')->willReturn(true);
        $this->container->has('config')->willReturn(true);
        $this->container->get('config')->willReturn($config);
        $this->container->get('ModuleManager')->willReturn($moduleManager);

        $model = $factory($this->container->reveal());

        $this->assertInstanceOf(ModuleModel::class, $model);
        $this->assertAttributeSame($moduleManager, 'moduleManager', $model);
        $this->assertAttributeEquals(array_keys($config['zf-rest']), 'restConfig', $model);
        $this->assertAttributeEquals(array_keys($config['zf-rpc']), 'rpcConfig', $model);
    }
}
