<?php

namespace ZF\ApiFirstAdmin\Model;

use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;

class RpcEndpointModelFactory
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @param  ModuleUtils $modules
     * @param  ConfigResource $config
     */
    public function __construct(ModuleUtils $modules, ConfigResourceFactory $configFactory)
    {
        $this->modules       = $modules;
        $this->configFactory = $configFactory;
    }

    /**
     * @param  string $module
     * @return RpcEndpointModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $config = $this->configFactory->factory($module);
        $this->models[$module] = new RpcEndpointModel($this->normalizeModuleName($module), $this->modules, $config);

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('.', '\\', $name);
    }
}
