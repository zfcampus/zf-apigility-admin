<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidatorsModelFactory implements FactoryInterface
{
    /**
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return object
     */
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('ValidatorManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the ValidatorManager service be present; service not found',
                get_class($this)
            ));
        }

        if (! $services->has('ZF\Apigility\Admin\Model\ValidatorMetadataModel')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s\ValidatorMetadataModel service be present; service not found',
                get_class($this),
                __NAMESPACE__
            ));
        }

        return new ValidatorsModel(
            $services->get('ValidatorManager'),
            $services->get('ZF\Apigility\Admin\Model\ValidatorMetadataModel')
        );
    }
}
