<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\Controller\VersioningController;
use ZF\Apigility\Admin\Model\VersioningModelFactory;

class VersioningControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return VersioningController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new VersioningController($container->get(VersioningModelFactory::class));
    }

    /**
     * @param ServiceLocatorInterface $container
     * @param null|string $cName
     * @param null|string $requestedName
     * @return VersioningController
     */
    public function createService(ServiceLocatorInterface $container, $cName = null, $requestedName = null)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, $requestedName ?: VersioningController::class);
    }
}
