<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Factory\ContentNegotiationResourceFactory;
use ZF\Apigility\Admin\Model\ContentNegotiationModel;
use ZF\Apigility\Admin\Model\ContentNegotiationResource;

class ContentNegotiationResourceFactoryTest extends TestCase
{
    public function setUp()
    {
        $this->container = $this->prophesize(ContainerInterface::class);
    }

    public function testFactoryRaisesExceptionIfContentNegotiationModelIsNotInContainer()
    {
        $factory = new ContentNegotiationResourceFactory();
        $this->container->has(ContentNegotiationModel::class)->willReturn(false);

        $this->setExpectedException(
            ServiceNotCreatedException::class,
            ContentNegotiationModel::class . ' service is not present'
        );

        $factory($this->container->reveal());
    }

    public function testFactoryReturnsConfiguredContentNegotiationResource()
    {
        $factory = new ContentNegotiationResourceFactory();
        $model = $this->prophesize(ContentNegotiationModel::class)->reveal();

        $this->container->has(ContentNegotiationModel::class)->willReturn(true);
        $this->container->get(ContentNegotiationModel::class)->willReturn($model);

        $resource = $factory($this->container->reveal());

        $this->assertInstanceOf(ContentNegotiationResource::class, $resource);
        $this->assertAttributeSame($model, 'model', $resource);
    }
}
