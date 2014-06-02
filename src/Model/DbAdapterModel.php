<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

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

    /**
     * @param ConfigResource $globalConfig
     * @param ConfigResource $localConfig
     */
    public function __construct(ConfigResource $globalConfig, ConfigResource $localConfig)
    {
        $this->globalConfig = $globalConfig;
        $this->localConfig = $localConfig;
    }

    /**
     * Create DB adapter configuration
     *
     * @param  mixed $name
     * @param  array $adapterConfig
     * @return DbAdapterEntity
     */
    public function create($name, array $adapterConfig)
    {
        $key = 'db.adapters.' . $name;

        if (strstr($adapterConfig['driver'], 'Pgsql')
            && isset($adapterConfig['charset'])
        ) {
            unset($adapterConfig['charset']);
        }

        $this->globalConfig->patchKey($key, array());
        $this->localConfig->patchKey($key, $adapterConfig);

        return new DbAdapterEntity($name, $adapterConfig);
    }

    /**
     * Update an existing DB adapter
     *
     * @param  string $name
     * @param  array $adapterConfig
     * @return DbAdapterEntity
     */
    public function update($name, array $adapterConfig)
    {
        return $this->create($name, $adapterConfig);
    }

    /**
     * Remove a named adapter
     *
     * @param  string $name
     * @return true
     */
    public function remove($name)
    {
        $key = 'db.adapters.' . $name;
        $this->globalConfig->deleteKey($key);
        $this->localConfig->deleteKey($key);
        return true;
    }

    /**
     * Retrieve all named adapters
     *
     * @return array
     */
    public function fetchAll()
    {
        $config = array();
        $fromConfigFile = $this->localConfig->fetch(true);
        if (isset($fromConfigFile['db'])
            && isset($fromConfigFile['db']['adapters'])
            && is_array($fromConfigFile['db']['adapters'])
        ) {
            $config = $fromConfigFile['db']['adapters'];
        }

        $adapters = array();
        foreach ($config as $name => $adapterConfig) {
            $adapters[] = new DbAdapterEntity($name, $adapterConfig);
        }

        return $adapters;
    }

    /**
     * Fetch configuration details for a named adapter
     *
     * @param  string $name
     * @return DbAdapterEntity
     */
    public function fetch($name)
    {
        $config = $this->localConfig->fetch(true);
        if (!isset($config['db'])
            || !isset($config['db']['adapters'])
            || !is_array($config['db']['adapters'])
            || !isset($config['db']['adapters'][$name])
            || !is_array($config['db']['adapters'][$name])
        ) {
            return false;
        }

        return new DbAdapterEntity($name, $config['db']['adapters'][$name]);
    }
}
