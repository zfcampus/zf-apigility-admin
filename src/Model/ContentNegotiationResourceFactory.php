<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class ContentNegotiationResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return ContentNegotiationResource
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ContentNegotiationModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                ContentNegotiationResource::class,
                ContentNegotiationModel::class
            ));
        }

        return new ContentNegotiationResource(
            $container->get(ContentNegotiationModel::class)
        );
    }
}
