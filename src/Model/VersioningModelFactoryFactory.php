<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ConfigResourceFactory;

/**
 * @deprecated since 1.5; use \ZF\Apigility\Admin\Model\ModuleVersioningModelFactoryFactory instead
 */
class VersioningModelFactoryFactory
{
    /**
     * @deprecated since 1.5.0; use the ModuleVersioningModelFactory instead
     * @param ContainerInterface $container
     * @return VersioningModelFactory
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ConfigResourceFactory::class)
            || ! $container->has(ModulePathSpec::class)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is missing one or more dependencies from ZF\Configuration',
                VersioningModelFactory::class
            ));
        }

        return new VersioningModelFactory(
            $container->get(ConfigResourceFactory::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
