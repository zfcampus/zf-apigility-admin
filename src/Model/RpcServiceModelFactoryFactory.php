<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ConfigResourceFactory;

class RpcServiceModelFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return RpcServiceModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ModulePathSpec::class)
            || ! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModuleModel::class)
            || ! $container->has('SharedEventManager')
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from ZF\Configuration',
                RpcServiceModelFactory::class
            ));
        }

        return new RpcServiceModelFactory(
            $container->get(ModulePathSpec::class),
            $container->get(ConfigResourceFactory::class),
            $container->get('SharedEventManager'),
            $container->get(ModuleModel::class)
        );
    }
}
