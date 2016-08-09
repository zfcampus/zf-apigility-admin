<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class DbAutodiscoveryModelFactory
{
    /**
     * @param ContainerInterface $container
     * @return DbAutodiscoveryModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because config service is not present',
                DbAutodiscoveryModel::class
            ));
        }

        $instance = new DbAutodiscoveryModel($container->get('config'));
        $instance->setServiceLocator($container);

        return $instance;
    }
}
