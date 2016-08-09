<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use Zend\Mvc\MvcEvent;

class NormalizeMatchedControllerServiceNameListener
{
    /**
     * @param MvcEvent $e
     * @return void
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches || ! $matches->getParam('controller_service_name')) {
            return;
        }

        // Replace '-' with namespace separator
        $controller = $matches->getParam('controller_service_name');
        $matches->setParam('controller_service_name', str_replace('-', '\\', $controller));
    }
}
