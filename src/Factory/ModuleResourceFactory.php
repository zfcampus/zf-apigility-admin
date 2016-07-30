<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\ModulePathSpec;
use ZF\Apigility\Admin\Model\ModuleResource;

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
