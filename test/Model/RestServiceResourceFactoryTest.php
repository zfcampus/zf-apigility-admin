<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Apigility\Admin\Model\InputFilterModel;
use ZF\Apigility\Admin\Model\RestServiceModelFactory;
use ZF\Apigility\Admin\Model\RestServiceResource;
use ZF\Apigility\Admin\Model\RestServiceResourceFactory;

class RestServiceResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionWhenMissingRestServicModelFactoryInContainer()
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . RestServiceModelFactory::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryRaisesExceptionWhenMissingInputFilterModelInContainer()
    {
        $factory = new RestServiceResourceFactory();

        $this->container->has(RestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(false);

        $this->expectException(ServiceNotCreatedException::class);
        $this->expectExceptionMessage('missing its ' . InputFilterModel::class. ' dependency');
        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredRestServiceResource()
    {
        $factory            = new RestServiceResourceFactory();
        $restFactory        = $this->prophesize(RestServiceModelFactory::class)->reveal();
        $inputFilterModel   = $this->prophesize(InputFilterModel::class)->reveal();
        $documentationModel = $this->prophesize(DocumentationModel::class)->reveal();

        $this->container->has(RestServiceModelFactory::class)->willReturn(true);
        $this->container->has(InputFilterModel::class)->willReturn(true);

        $this->container->get(RestServiceModelFactory::class)->willReturn($restFactory);
        $this->container->get(InputFilterModel::class)->willReturn($inputFilterModel);
        $this->container->get(DocumentationModel::class)->willReturn($documentationModel);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(RestServiceResource::class, $resource);
        $this->assertAttributeSame($restFactory, 'restFactory', $resource);
        $this->assertAttributeSame($inputFilterModel, 'inputFilterModel', $resource);
        $this->assertAttributeSame($documentationModel, 'documentationModel', $resource);
    }
}
