<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidatorsModelFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('ValidatorManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s\\ValidatorsModel requires that the ValidatorManager service be present; service not found',
                __NAMESPACE__
            ));
        }
        return new ValidatorsModel($services->get('ValidatorManager'));
    }
}
