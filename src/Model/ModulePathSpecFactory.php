<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ModuleUtils;

class ModulePathSpecFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModulePathSpec
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has(ModuleUtils::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because %s service is not present',
                ModulePathSpec::class,
                ModuleUtils::class
            ));
        }

        $config = $this->getConfig($container);

        return new ModulePathSpec(
            $container->get(ModuleUtils::class),
            $this->getPathSpecFromConfig($config),
            $this->getPathFromConfig($config)
        );
    }

    /**
     * Retrieve the zf-apigility-admin configuration array, if present.
     *
     * @param ContainerInterface $container
     * @return array
     */
    private function getConfig(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            return [];
        }

        $config = $container->get('config');

        if (! isset($config['zf-apigility-admin'])
            || ! is_array($config['zf-apigility-admin'])
        ) {
            return [];
        }

        return $config['zf-apigility-admin'];
    }

    /**
     * @param array $config
     * @return string Value of 'path_spec'; defaults to psr-0
     */
    private function getPathSpecFromConfig(array $config)
    {
        return isset($config['path_spec']) ? $config['path_spec'] : 'psr-0';
    }

    /**
     * @param array $config
     * @return string '.' if no module_path found in configuration, otherwise
     *     value of module_path.
     * @throws ServiceNotCreatedException if configured module_path is not a
     *     valid directory.
     */
    private function getPathFromConfig(array $config)
    {
        $default = '.';

        if (! isset($config['module_path'])) {
            return $default;
        }

        if (! is_dir($config['module_path'])) {
            throw new ServiceNotCreatedException(sprintf(
                'Invalid module path "%s"; does not exist',
                $config['module_path']
            ));
        }

        return $config['module_path'];
    }
}
