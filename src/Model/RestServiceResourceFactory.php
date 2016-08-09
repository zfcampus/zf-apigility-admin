<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class RestServiceResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return RestServiceResource
     * @throws ServiceNotCreatedException
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
