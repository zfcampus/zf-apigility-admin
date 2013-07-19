<?php

namespace ZFApiFirstAdmin;

use Zend\Console\Adapter\AdapterInterface as ConsoleAdapterInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;
use Zend\ModuleManager\Feature\ConsoleUsageProviderInterface;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class Module implements AutoloaderProviderInterface, ConfigProviderInterface /*, ConsoleUsageProviderInterface */
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $sm;

    public function onBootstrap(MvcEvent $e)
    {
        $this->sm = $e->getApplication()->getServiceManager();
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }

    public function getDiagnostics()
    {
        return array(
            'Config File Writable' => function () {
                if (!defined('APPLICATION_PATH')) {
                    return false;
                }
                if (!is_writable(APPLICATION_PATH . '/config/autoload/development.php')) {
                    return false;
                }
                return true;
            },
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

}