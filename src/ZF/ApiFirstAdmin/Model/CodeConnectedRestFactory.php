<?php

namespace ZF\ApiFirstAdmin\Model;

class CodeConnectedRestFactory extends CodeConnectedRpcFactory
{
    /**
     * @param  string $module
     * @return CodeConnectedRest
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $config = $this->configFactory->factory($module);
        $this->models[$module] = new CodeConnectedRest($this->normalizeModuleName($module), $this->modules, $config);

        return $this->models[$module];
    }
}
