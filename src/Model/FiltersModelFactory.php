<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FiltersModelFactory implements FactoryInterface
{
    /**
     * Return a filter plugin manager model instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return FiltersModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('FilterManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the FilterManager service be present; service not found',
                get_class($this)
            ));
        }

        $metadata = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            if (isset($config['filter_metadata'])
                && is_array($config['filter_metadata'])
            ) {
                $metadata = $config['filter_metadata'];
            }
        }

        return new FiltersModel($container->get('FilterManager'), $metadata);
    }

    /**
     * Return a filter plugin manager model instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return FiltersModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, FiltersModel::class);
    }
}
