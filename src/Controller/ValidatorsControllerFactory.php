<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\Model\ValidatorsModel;

class ValidatorsControllerFactory implements FactoryInterface
{
    /**
     * Create and return a ValidatorsController instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ValidatorsController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ValidatorsController($container->get(ValidatorsModel::class));
    }

    /**
     * Create and return a ValidatorsController instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ValidatorsController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, ValidatorsController::class);
    }
}
