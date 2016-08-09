<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class DoctrineAdapterResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return DoctrineAdapterResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(DoctrineAdapterModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                DoctrineAdapterResource::class,
                DoctrineAdapterModel::class
            ));
        }

        $model = $container->get(DoctrineAdapterModel::class);

        $modules = $container->get('ModuleManager');
        $loadedModules = $modules->getLoadedModules(false);

        $resource = new DoctrineAdapterResource($model, $loadedModules);

        // @todo Remove once setServiceLocator and getServiceLocator are removed
        //     from the DoctrineAdapterResource class.
        $resource->setServiceLocator($container);

        return $resource;
    }
}
