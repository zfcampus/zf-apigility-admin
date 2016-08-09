<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use Zend\Http\Header\GenericHeader;
use Zend\Http\Header\GenericMultiHeader;
use Zend\Http\Headers;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\RouteMatch;

class DisableHttpCacheListener
{
    /**
     * @param MvcEvent $e
     * @return void
     */
    public function __invoke(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();
        if (! ($matches instanceof RouteMatch || $matches instanceof V2RouteMatch)) {
            // In 404's, we do not have a route match... nor do we need to do
            // anything
            return;
        }

        if (! $matches->getParam('is_apigility_admin_api', false)) {
            // Not part of the Apigility Admin API; nothing to do
            return;
        }

        $request = $e->getRequest();
        if (! ($request->isGet() || $request->isHead())) {
            return;
        }

        $this->disableHttpCache($e->getResponse()->getHeaders());
    }

    /**
     * Prepare cache-busting headers for GET requests
     *
     * Invoked from the onFinish() method for GET requests to disable client-side HTTP caching.
     *
     * @param Headers $headers
     */
    protected function disableHttpCache(Headers $headers)
    {
        $headers->addHeader(new GenericHeader('Expires', '0'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'no-store, no-cache, must-revalidate'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'post-check=0, pre-check=0'));
        $headers->addHeaderLine('Pragma', 'no-cache');
    }
}
