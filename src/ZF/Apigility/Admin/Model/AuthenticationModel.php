<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ConfigResource;
use ZF\Rest\Exception\CreationException;
use ZF\Rest\Exception\PatchException;

class AuthenticationModel
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
     * Create authentication configuration
     *
     * @param  array $authenticationConfig
     * @return AuthenticationEntity
     */
    public function create(array $authenticationConfig)
    {
        if ($this->fetch() instanceof AuthenticationEntity) {
            throw new CreationException('Authentication already exists', 409);
        }

        $entity = $this->createAuthenticationEntityFromConfig($authenticationConfig);
        $global = $entity->getArrayCopy();
        $local  = $this->removeSensitiveConfig($global);
        $key    = 'zf-mvc-auth.authentication.http';

        $this->globalConfig->patchKey($key, $global);
        $this->localConfig->patchKey($key, $local);

        return $entity;
    }

    /**
     * Update authentication configuration
     *
     * @param  array $authenticationConfig
     * @return AuthenticationEntity
     */
    public function update(array $authenticationConfig)
    {
        $current = $this->fetch();
        if (! $current instanceof AuthenticationEntity) {
            return $this->create($authenticationConfig);
        }

        $current->exchangeArray($authenticationConfig);
        $global = $current->getArrayCopy();
        $local  = $this->removeSensitiveConfig($global);
        $key    = 'zf-mvc-auth.authentication.http';

        $this->globalConfig->patchKey($key, $global);
        $this->localConfig->patchKey($key, $local);

        return $current;
    }

    /**
     * Remove authentication
     *
     * @return true
     */
    public function remove()
    {
        $key = 'zf-mvc-auth.authentication.http';
        $this->globalConfig->deleteKey($key);
        $this->localConfig->deleteKey($key);
        return true;
    }

    /**
     * Fetch configuration details for authentication
     *
     * @return AuthenticationEntity
     */
    public function fetch()
    {
        $config = $this->localConfig->fetch(true);
        if (!isset($config['zf-mvc-auth'])
            || !isset($config['zf-mvc-auth']['authentication'])
            || !is_array($config['zf-mvc-auth']['authentication'])
            || !isset($config['zf-mvc-auth']['authentication']['http'])
            || !is_array($config['zf-mvc-auth']['authentication']['http'])
            || !isset($config['zf-mvc-auth']['authentication']['http']['accept_schemes'])
            || !is_array($config['zf-mvc-auth']['authentication']['http']['accept_schemes'])
        ) {
            return false;
        }

        $config = $config['zf-mvc-auth']['authentication']['http'];
        return $this->createAuthenticationEntityFromConfig($config);
    }

    /**
     * Create an AuthenticationEntity based on the configuration given
     * 
     * @param  array $config 
     * @return AuthenticationEntity
     */
    protected function createAuthenticationEntityFromConfig(array $config)
    {
        $type   = array_shift($config['accept_schemes']);
        $realm  = isset($config['realm']) ? $config['realm'] : 'api';

        return new AuthenticationEntity($type, $realm, $config);
    }

    /**
     * Remove sensitive information from the configuration
     *
     * Currently only "htpasswd" and "htdigest" entries are stripped.
     * 
     * @param  array $config 
     * @return array
     */
    protected function removeSensitiveConfig(array $config)
    {
        if (isset($config['htpasswd'])) {
            unset($config['htpasswd']);
        }
        if (isset($config['htdigest'])) {
            unset($config['htdigest']);
        }
        return $config;
    }
}
