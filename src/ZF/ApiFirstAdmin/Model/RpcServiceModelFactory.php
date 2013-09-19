<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\EventManager\SharedEventManagerInterface;
use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;

class RpcServiceModelFactory
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = array();

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @var SharedEventManagerInterface
     */
    protected $sharedEventManager;

    /**
     * @param  ModuleUtils $modules
     * @param  ConfigResource $config
     */
    public function __construct(ModuleUtils $modules, ConfigResourceFactory $configFactory, SharedEventManagerInterface $sharedEvents)
    {
        $this->modules            = $modules;
        $this->configFactory      = $configFactory;
        $this->sharedEventManager = $sharedEvents;
    }

    /**
     * @param  string $module
     * @return RpcServiceModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $config = $this->configFactory->factory($module);
        $this->models[$module] = new RpcServiceModel($this->normalizeModuleName($module), $this->modules, $config);

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
