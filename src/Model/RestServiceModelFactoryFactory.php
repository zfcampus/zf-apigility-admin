<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Doctrine\Admin\Model\DoctrineRestServiceModel;
use ZF\Configuration\ConfigResourceFactory;

class RestServiceModelFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return RestServiceModelFactory
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
                RestServiceModelFactory::class
            ));
        }

        $sharedEvents = $container->get('SharedEventManager');
        $this->attachSharedListeners($sharedEvents, $container);

        return new RestServiceModelFactory(
            $container->get(ModulePathSpec::class),
            $container->get(ConfigResourceFactory::class),
            $sharedEvents,
            $container->get(ModuleModel::class)
        );
    }

    /**
     * Attach shared listeners to the RestServiceModel.
     *
     * @param SharedEventManagerInterface $sharedEvents
     * @param ContainerInterface $container
     * @return void
     */
    private function attachSharedListeners(SharedEventManagerInterface $sharedEvents, ContainerInterface $container)
    {
        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DbConnectedRestServiceModel::class, 'onFetch']
        );

        $modules = $container->get('ModuleManager');
        $loaded = $modules->getLoadedModules(false);
        if (! isset($loaded['ZF\Apigility\Doctrine\Admin'])) {
            return;
        }

        // Wire Doctrine-Connected fetch listener
        $sharedEvents->attach(
            RestServiceModel::class,
            'fetch',
            [DoctrineRestServiceModel::class, 'onFetch']
        );
    }
}
