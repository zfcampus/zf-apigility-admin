<?php

namespace ZF\ApiFirstAdmin;

use Zend\Mvc\MvcEvent;

class Module
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
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
        return include __DIR__ . '/../../../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array('factories' => array(
            'ZF\ApiFirstAdmin\Model\ApiFirstModule' => function ($services) {
                if (!$services->has('ModuleManager')) {
                    throw new ServiceNotCreatedException(
                        'Cannot create ZF\ApiFirstAdmin\Model\ApiFirstModule service because ModuleManager service is not present'
                    );
                }
                $modules    = $services->get('ModuleManager');
                $restConfig = array();
                $rpcConfig  = array();
                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['zf-rest'])) {
                        $restConfig = $config['zf-rest'];
                    }
                    if (isset($config['zf-rpc'])) {
                        $rpcConfig = $config['zf-rpc'];
                    }
                }
                return new Model\ApiFirstModule($modules, $restConfig, $rpcConfig);
            },
            'ZF\ApiFirstAdmin\Model\ApiFirstModuleListener' => function ($services) {
                $moduleModel = $services->get('ZF\ApiFirstAdmin\Model\ApiFirstModule');
                $listener    = new Model\ApiFirstModuleListener($moduleModel);

                if ($services->has('Config')) {
                    $config = $services->get('Config');
                    if (isset($config['zf-api-first-admin'])) {
                        if (isset($config['zf-api-first-admin']['module_path'])) {
                            $listener->setModulePath($config['zf-api-first-admin']['module_path']);
                        }
                    }
                }
                return $listener;
            },
        ));
    }
}
