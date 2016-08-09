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

abstract class AbstractPluginManagerModelFactory implements FactoryInterface
{
    /**
     * @var string
     */
    protected $pluginManagerService;

    /**
     * @var string
     */
    protected $pluginManagerModel;

    /**
     * Return one of the plugin manager-backed model instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return mixed A model instance that composes a plugin manager.
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (null === $this->pluginManagerService
            || null === $this->pluginManagerModel
            || ! class_exists($this->pluginManagerModel)
        ) {
            throw new ServiceNotCreatedException(sprintf(
                '%s is an invalid factory; please check the $pluginManagerService and/or $pluginManagerModel values',
                get_class($this)
            ));
        }

        if (! $container->has($this->pluginManagerService)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                $this->pluginManagerService
            ));
        }

        $class = $this->pluginManagerModel;
        return new $class($container->get($this->pluginManagerService));
    }

    /**
     * Return one of the plugin manager-backed model instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return mixed A model instance that composes a plugin manager.
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, $this->pluginManagerModel);
    }
}
