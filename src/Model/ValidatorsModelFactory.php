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

class ValidatorsModelFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorsModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorsModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has('ValidatorManager')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the ValidatorManager service be present; service not found',
                get_class($this)
            ));
        }

        if (! $container->has(ValidatorMetadataModel::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                ValidatorMetadataModel::class
            ));
        }

        return new ValidatorsModel(
            $container->get('ValidatorManager'),
            $container->get(ValidatorMetadataModel::class)
        );
    }

    /**
     * Create and return a ValidatorsModel instance v2.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorsModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ValidatorsModel::class);
    }
}
