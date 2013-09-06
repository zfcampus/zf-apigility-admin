<?php

namespace ZF\ApiFirstAdmin\Model;

class RestEndpointModelFactory extends RpcEndpointModelFactory
{
    /**
     * @param  string $module
     * @return RestEndpointModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $config = $this->configFactory->factory($module);
        $this->models[$module] = new RestEndpointModel($this->normalizeModuleName($module), $this->modules, $config);

        return $this->models[$module];
    }
}
