<?php

namespace ZF\ApiFirstAdmin\Model;

use ZF\Configuration\ConfigResource;

class DbAdapterModel
{
    /**
     * @var ConfigResource
     */
    protected $globalConfig;

    /**
     * @var ConfigResource
     */
    protected $localConfig;

    public function __construct(ConfigResource $globalConfig, ConfigResource $localConfig)
    {
        $this->globalConfig = $globalConfig;
        $this->localConfig = $localConfig;
    }

    public function create($name, array $adapterConfig)
    {
        $key = 'db.adapters.' . $name;

        $this->globalConfig->patchKey($key, array());
        $this->localConfig->patchKey($key, $adapterConfig);

        return new DbAdapterEntity($name, $adapterConfig);
    }
}
