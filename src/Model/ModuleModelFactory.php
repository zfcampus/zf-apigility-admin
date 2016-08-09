<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;

class ModuleModelFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModuleModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('ModuleManager')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because ModuleManager service is not present',
                ModuleModel::class
            ));
        }

        $config = $this->getConfig($container);

        return new ModuleModel(
            $container->get('ModuleManager'),
            $this->getNamedConfigArray('zf-rest', $config),
            $this->getNamedConfigArray('zf-rpc', $config)
        );
    }

    /**
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        return $container->has('config') ? $container->get('config') : [];
    }

    /**
     * @param string $name Config key to retrieve
     * @param array $config
     * @return array
     */
    private function getNamedConfigArray($name, array $config)
    {
        return (isset($config[$name]) && is_array($config[$name]))
            ? $config[$name]
            : [];
    }
}
