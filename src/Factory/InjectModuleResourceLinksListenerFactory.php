<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Factory;

use Interop\Container\ContainerInterface;
use ZF\Apigility\Admin\Listener\InjectModuleResourceLinksListener;

class InjectModuleResourceLinksListenerFactory
{
    /**
     * @param ContainerInterface $container
     */
    public function __invoke(ContainerInterface $container)
    {
        return new InjectModuleResourceLinksListener(
            $container->get('ViewHelperManager')
        );
    }
}
