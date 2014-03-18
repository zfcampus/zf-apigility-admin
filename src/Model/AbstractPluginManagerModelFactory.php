<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class AbstractPluginManagerModelFactory implements FactoryInterface
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
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return object
     */
    public function createService(ServiceLocatorInterface $services)
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

        if (! $services->has($this->pluginManagerService)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                get_class($this),
                $this->pluginManagerService
            ));
        }

        $class = $this->pluginManagerModel;
        return new $class($services->get($this->pluginManagerService));
    }
}
