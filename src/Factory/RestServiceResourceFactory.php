<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\Apigility\Admin\Model\InputFilterModel;
use ZF\Apigility\Admin\Model\RestServiceModelFactory;
use ZF\Apigility\Admin\Model\RestServiceResource;

class RestServiceResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return RestServiceResource
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(RestServiceModelFactory::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                RestServiceModelFactory::class
            ));
        }
        if (! $container->has(InputFilterModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency and cannot be created',
                RestServiceResource::class,
                InputFilterModel::class
            ));
        }

        return new RestServiceResource(
            $container->get(RestServiceModelFactory::class),
            $container->get(InputFilterModel::class),
            $container->get(DocumentationModel::class)
        );
    }
}
