<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Configuration\ConfigResourceFactory;

class ModuleConfigControllerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return ModuleConfigController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new ModuleConfigController($container->get(ConfigResourceFactory::class));
    }

    /**
     * @param ServiceLocatorInterface $container
     * @return ModuleConfigController
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, ModuleConfigController::class);
    }
}
