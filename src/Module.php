<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin;

use Zend\ModuleManager\ModuleManagerInterface;
use Zend\Mvc\MvcEvent;

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
     * @var \Interop\Container\ContainerInterface
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
        $loaded = $modules->getLoadedModules(false);
        if (! isset($loaded['ZF\Apigility\Admin\Ui'])) {
            $modules->loadModule('ZF\Apigility\Admin\Ui');
        }

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
        $events->attach(
            MvcEvent::EVENT_FINISH,
            $services->get(Listener\DisableHttpCacheListener::class),
            1000
        );
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
}
