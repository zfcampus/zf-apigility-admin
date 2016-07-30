<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Apigility\Admin\Model\DoctrineAdapterModel;
use ZF\Apigility\Admin\Model\DoctrineAdapterResource;

class DoctrineAdapterResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return DoctrineAdapterResource
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $services->has(DoctrineAdapterModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                DoctrineAdapterResource::class,
                DoctrineAdapterModel::class
            ));
        }

        $model = $services->get(DoctrineAdapterModel::class);

        $modules = $services->get('ModuleManager');
        $loadedModules = $modules->getLoadedModules(false);

        return new DoctrineAdapterResource($model, $loadedModules);
    }
}
