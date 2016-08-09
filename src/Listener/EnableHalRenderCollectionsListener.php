<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use Zend\Mvc\MvcEvent;

class EnableHalRenderCollectionsListener
{
    /**
     * Ensure the render_collections flag of the HAL view helper is enabled
     * regardless of the configuration setting if we match an admin service.
     *
     * @param MvcEvent $e
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! $matches
            || 0 !== strpos($matches->getParam('controller'), 'ZF\Apigility\Admin\\')
        ) {
            return;
        }

        $app      = $e->getTarget();
        $services = $app->getServiceManager();
        $helpers  = $services->get('ViewHelperManager');
        $hal      = $helpers->get('Hal');
        $hal->setRenderCollections(true);
    }
}
