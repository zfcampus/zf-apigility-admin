<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ConfigResource;

class DoctrineAdapterModel
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
     * Create Doctrine adapter configuration
     *
     * @param $name
     * @param array $adapterConfig
     * @return DoctrineAdapterEntity
     */
    public function create($name, array $adapterConfig)
    {
        $key = "doctrine.connection.{$name}";
        $this->globalConfig->patchKey($key, array());
        $this->localConfig->patchKey($key, $adapterConfig);

        return new DoctrineAdapterEntity($name, $adapterConfig);
    }

    /**
     * Update an existing Doctrine adapter
     *
     * @param $name
     * @param array $adapterConfig
     * @return DoctrineAdapterEntity
     */
    public function update($name, array $adapterConfig)
    {
        return $this->create($name, $adapterConfig);
    }

    /**
     * Remove a named adapter
     *
     * @param $name
     * @return bool
     */
    public function remove($name)
    {
        $key = "doctrine.connection.{$name}";
        $this->globalConfig->deleteKey($key);
        $this->localConfig->deleteKey($key);
        return true;
    }

    /**
     * Retrieve all named adapters
     *
     * @return array|bool
     */
    public function fetchAll()
    {
        $fromConfigFile = $this->localConfig->fetch(true);
        if (isset($fromConfigFile['doctrine'])
            && isset($fromConfigFile['doctrine']['connection'])
            && is_array($fromConfigFile['doctrine']['connection'])
        ) {
            foreach ($fromConfigFile['doctrine']['connection'] as $connection) {
                if (! is_array($connection)) {
                    return false;
                }

                // 'driverClass' is part of ORM configuration, and MUST be provided by the user;
                // 'connectionString' is part of ODM configuration, and MUST be provided by the user.
                // As such, absence of either of these means we do not have a valid connection.
                if (! isset($connection['driverClass']) && ! isset($connection['connectionString'])) {
                    return false;
                }
            }
            $config = $fromConfigFile['doctrine']['connection'];
        } else {
            return false;
        }

        $adapters = array();
        foreach ($config as $name => $adapterConfig) {
            $adapters[] = new DoctrineAdapterEntity($name, $adapterConfig);
        }

        return $adapters;
    }

    /**
     * Fetch configuration details for a named adapter
     *
     * @param $name
     * @return bool|DoctrineAdapterEntity
     */
    public function fetch($name)
    {
        $config = $this->localConfig->fetch(true);
        if (!isset($config['doctrine'])
            || !isset($config['doctrine']['connection'])
            || !is_array($config['doctrine']['connection'])
            || !isset($config['doctrine']['connection'][$name])
            || !is_array($config['doctrine']['connection'][$name])
        ) {
            return false;
        }
        return new DoctrineAdapterEntity($name, $config['doctrine']['connection'][$name]);
    }
}
