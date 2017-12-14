<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\ModuleResource;
use ZF\Apigility\Admin\Model\ModuleResourceFactory;

class ModuleResourceFactoryTest extends TestCase
{
    public function testFactoryReturnsConfiguredModuleResource()
    {
        $factory = new ModuleResourceFactory();
        $model = $this->prophesize(ModuleModel::class)->reveal();
        $pathSpec = $this->prophesize(ModulePathSpec::class)->reveal();
        $container = $this->prophesize(ContainerInterface::class);

        $container->get(ModuleModel::class)->willReturn($model);
        $container->get(ModulePathSpec::class)->willReturn($pathSpec);

        $resource = $factory($container->reveal());

        $this->assertInstanceOf(ModuleResource::class, $resource);
        $this->assertAttributeSame($model, 'modules', $resource);
        $this->assertAttributeSame($pathSpec, 'modulePathSpec', $resource);
    }
}
