<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\ModuleManager\ModuleManager;
use ZF\ApiFirst\ApiFirstModuleInterface;

class ApiFirstModule
{
    /**
     * Endpoints for each module
     * @var array
     */
    protected $endpoints = array();

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

    /**
     * @var array
     */
    protected $modules;

    /**
     * @var array
     */
    protected $restConfig;

    /**
     * @var array
     */
    protected $rpcConfig;

    /**
     * @param  ModuleManager $moduleManager
     * @param  array $restConfig
     * @param  array $rpcConfig
     */
    public function __construct(ModuleManager $moduleManager, array $restConfig, array $rpcConfig)
    {
        $this->moduleManager = $moduleManager;
        $this->restConfig    = array_keys($restConfig);
        $this->rpcConfig     = array_keys($rpcConfig);
    }

    /**
     * Returns list of all API-First-enabled modules
     *
     * @return array
     */
    public function getEnabledModules()
    {
        if (is_array($this->modules)) {
            return $this->modules;
        }

        $this->modules = array();
        foreach ($this->moduleManager->getLoadedModules() as $moduleName => $module) {
            if ($module instanceof ApiFirstModuleInterface) {
                $this->modules[] = $moduleName;
            }
        }

        return $this->modules;
    }

    /**
     * Retrieve all endpoints for a given module
     *
     * Returns null if the module is not API-enabled.
     *
     * Returns an array with the elements "rest" and "rpc" on success, with
     * each being an array of controller service names.
     *
     * @param  string $module
     * @return null|array
     */
    public function getEndpointsByModule($module)
    {
        if (isset($this->endpoints[$module])) {
            return $this->endpoints[$module];
        }

        $modules = $this->getEnabledModules();
        if (!in_array($module, $modules)) {
            return null;
        }

        $endpoints = array(
            'rest' => $this->discoverEndpointsByModule($module, $this->restConfig),
            'rpc'  => $this->discoverEndpointsByModule($module, $this->rpcConfig),
        );
        $this->endpoints[$module] = $endpoints;

        return $endpoints;
    }

    /**
     * Retrieve a list of all API-First-enabled modules with their associated
     * endpoints.
     *
     * @return array
     */
    public function getEndpointsSortedByModule()
    {
        foreach ($this->getEnabledModules() as $module) {
            $this->getEndpointsByModule($module);
        }
        return $this->endpoints;
    }

    /**
     * Loops through an array of controllers, determining which match the given module.
     *
     * @param  string $module
     * @param  array $config
     * @return array
     */
    protected function discoverEndpointsByModule($module, array $config)
    {
        $endpoints = array();
        foreach ($config as $controller) {
            if (strpos($controller, $module) === 0) {
                $endpoints[] = $controller;
            }
        }
        return $endpoints;
    }
}
