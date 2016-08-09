<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;

class ModuleResourceFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModuleResource
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ModuleResource(
            $container->get(ModuleModel::class),
            $container->get(ModulePathSpec::class)
        );
    }
}
