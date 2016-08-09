<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class DbAdapterResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return DbAdapterResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(DbAdapterModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                DbAdapterResource::class,
                DbAdapterModel::class
            ));
        }
        return new DbAdapterResource($container->get(DbAdapterModel::class));
    }
}
