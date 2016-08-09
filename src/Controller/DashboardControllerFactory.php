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
use ZF\Apigility\Admin\Model;

class DashboardControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DashboardController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new DashboardController(
            $container->get(Model\AuthenticationModel::class),
            $container->get(Model\ContentNegotiationModel::class),
            $container->get(Model\DbAdapterModel::class),
            $container->get(Model\ModuleModel::class),
            $container->get(Model\RestServiceModelFactory::class),
            $container->get(Model\RpcServiceModelFactory::class)
        );
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return DashboardController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }

        return $this($container, DashboardController::class);
    }
}
