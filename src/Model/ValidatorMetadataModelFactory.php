<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidatorMetadataModelFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorMetadataModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorMetadataModel
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $metadata = [];
        if ($container->has('config')) {
            $config = $container->get('config');
            if (isset($config['validator_metadata'])) {
                $metadata = $config['validator_metadata'];
            }
        }

        return new ValidatorMetadataModel($metadata);
    }

    /**
     * Create and return a ValidatorMetadataModel instance.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorMetadataModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ValidatorMetadataModel::class);
    }
}
