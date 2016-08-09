<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ConfigResourceFactory;

class ModuleVersioningModelFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModuleVersioningModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModulePathSpec::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from ZF\Configuration',
                ModuleVersioningModelFactory::class
            ));
        }

        return new ModuleVersioningModelFactory(
            $container->get(ConfigResourceFactory::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
