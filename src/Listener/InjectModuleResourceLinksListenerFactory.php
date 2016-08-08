<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use Interop\Container\ContainerInterface;

class InjectModuleResourceLinksListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return InjectModuleResourceLinksListener
     */
    public function __invoke(ContainerInterface $container)
    {
        return new InjectModuleResourceLinksListener(
            $container->get('ViewHelperManager')
        );
    }
}
