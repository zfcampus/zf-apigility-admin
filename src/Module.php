<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin;

use Zend\Http\Header\GenericHeader;
use Zend\Http\Header\GenericMultiHeader;
use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteMatch as V2RouteMatch;
use Zend\Router\RouteMatch;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\Entity;
use ZF\Hal\View\HalJsonModel;

class Module
{
    /**
     * @var MvcEvent
     */
    protected $mvcEvent;

    /**
     * @var callable
     */
    protected $urlHelper;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $sm;

    /**
     * Initialize module.
     *
     * If the admin UI module is not loaded yet, load it.
     *
     * Disable the opcache as well.
     *
     * @param ModuleManagerInterface $modules
     */
    public function init(ModuleManagerInterface $modules)
    {
        $this->disableOpCache();
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $app      = $e->getApplication();
        $this->sm = $services = $app->getServiceManager();
        $events   = $app->getEventManager();

        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\NormalizeMatchedControllerServiceNameListener::class),
            -20
        );
        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\NormalizeMatchedInputFilterNameListener::class),
            -20
        );
        $events->attach(
            MvcEvent::EVENT_ROUTE,
            $services->get(Listener\EnableHalRenderCollectionsListener::class),
            -1000
        );
        $events->attach(
            MvcEvent::EVENT_RENDER,
            $services->get(Listener\InjectModuleResourceLinksListener::class),
            100
        );
        $events->attach(MvcEvent::EVENT_FINISH, [$this, 'onFinish'], 1000);
        $this->sm->get(Listener\CryptFilterListener::class)->attach($events);
    }

    /**
     * Run diagnostics
     *
     * @return array|bool
     */
    public function getDiagnostics()
    {
        return [
            'Config File Writable' => function () {
                if (! defined('APPLICATION_PATH')) {
                    return false;
                }
                if (! is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        ];
    }

    /**
     * Tell browsers not to cache responses from the admin API
     *
     * @param  \Zend\Mvc\MvcEvent $e
     */
    public function onFinish($e)
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
        if ($request->isGet() || $request->isHead()) {
            $this->disableHttpCache($e->getResponse()->getHeaders());
        }
    }


    /**
     * Disable opcode caching
     *
     * Disables opcode caching for opcode caches that allow doing so during
     * runtime; the admin API will not work with opcode caching enabled.
     */
    protected function disableOpCache()
    {
        if (isset($_SERVER['SERVER_SOFTWARE'])
            && preg_match('/^PHP .*? Development Server$/', $_SERVER['SERVER_SOFTWARE'])
        ) {
            // skip the built-in PHP webserver (OPcache reset is not needed +
            // it crashes the server in PHP 5.4 with ZendOptimizer+)
            return;
        }

        // Disable opcode caches that allow runtime disabling

        if (function_exists('xcache_get')) {
            // XCache; just disable it
            ini_set('xcache.cacher', '0');
            return;
        }

        if (function_exists('wincache_ocache_meminfo')) {
            // WinCache; just disable it
            ini_set('wincache.ocenabled', '0');
            return;
        }
    }

    /**
     * Prepare cache-busting headers for GET requests
     *
     * Invoked from the onFinish() method for GET requests to disable client-side HTTP caching.
     *
     * @param \Zend\Http\Headers $headers
     */
    protected function disableHttpCache($headers)
    {
        $headers->addHeader(new GenericHeader('Expires', '0'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'no-store, no-cache, must-revalidate'));
        $headers->addHeader(new GenericMultiHeader('Cache-Control', 'post-check=0, pre-check=0'));
        $headers->addHeaderLine('Pragma', 'no-cache');
    }
}
