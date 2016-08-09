<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class RpcServiceResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return RpcServiceResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(RpcServiceModelFactory::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency',
                RpcServiceResource::class,
                RpcServiceModelFactory::class
            ));
        }
        if (! $container->has(InputFilterModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its %s dependency',
                RpcServiceResource::class,
                InputFilterModel::class
            ));
        }
        if (! $container->has('ControllerManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing its ControllerManager dependency',
                RpcServiceResource::class
            ));
        }

        return new RpcServiceResource(
            $container->get(RpcServiceModelFactory::class),
            $container->get(InputFilterModel::class),
            $container->get('ControllerManager'),
            $container->get(DocumentationModel::class)
        );
    }
}
