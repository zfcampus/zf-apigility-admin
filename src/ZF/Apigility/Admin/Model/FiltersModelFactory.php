<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FiltersModelFactory implements FactoryInterface
{
    /**
     * Return a filter plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return FiltersModel
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('FilterManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the FilterManager service be present; service not found',
                get_class($this)
            ));
        }

        $metadata = array();
        if ($services->has('Config')) {
            $config = $services->get('Config');
            if (isset($config['filter_metadata'])
                && is_array($config['filter_metadata'])
            ) {
                $metadata = $config['filter_metadata'];
            }
        }

        return new FiltersModel($services->get('FilterManager'), $metadata);
    }
}
