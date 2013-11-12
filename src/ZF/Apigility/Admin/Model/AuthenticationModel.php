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

        $entity  = $this->createAuthenticationEntityFromConfig($authenticationConfig);
        $allData = $entity->getArrayCopy();
        $global  = $this->removeSensitiveConfig($allData);
        $local   = array_udiff_assoc($allData, $global, sprintf('%s::arrayDiffRecursive', __CLASS__));
        switch (true) {
            case $entity->isBasic():
            case $entity->isDigest():
                $this->patchHttpAuthConfig($entity, $global, $local);
                break;
            case $entity->isOAuth2():
                $this->patchOAuth2Config($entity, $global, $local);
                break;
        }

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
        $allData = $current->getArrayCopy();
        $global  = $this->removeSensitiveConfig($allData);
        $local   = array_udiff_assoc($allData, $global, sprintf('%s::arrayDiffRecursive', __CLASS__));
        switch (true) {
            case $current->isBasic():
            case $current->isDigest():
                $this->patchHttpAuthConfig($current, $global, $local);
                break;
            case $entity->isOAuth2():
                $this->patchOAuth2Config($current, $global, $local);
                break;
        }

        return $current;
    }

    /**
     * Remove authentication
     *
     * @return true
     */
    public function remove()
    {
        $configKeys = array(
            'zf-mvc-auth.authentication.http',
            'zf-mvc-auth.authentication.oauth2',
            'router.routes.oauth',
        );
        foreach ($configKeys as $key) {
            $this->globalConfig->deleteKey($key);
            $this->localConfig->deleteKey($key);
        }
        return true;
    }

    /**
     * Fetch configuration details for authentication
     *
     * @return AuthenticationEntity
     */
    public function fetch()
    {
        $config = $this->globalConfig->fetch(true);
        if (isset($config['zf-mvc-auth']['authentication']['http'])) {
            $config = $this->fetchHttpAuthConfiguration($config);
        } else {
            $config = $this->fetchOAuth2Configuration($config);
        }

        if (!$config) {
            return false;
        }

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
        switch (true) {
            case (isset($config['accept_schemes'])):
                $type   = array_shift($config['accept_schemes']);
                $realm  = isset($config['realm']) ? $config['realm'] : 'api';
                return new AuthenticationEntity($type, $realm, $config);
            case (isset($config['dsn'])):
                return new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, $config);
        }
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
        foreach (array_keys($config) as $key) {
            switch ($key) {
                case 'dsn':
                case 'htdigest':
                case 'htpasswd':
                case 'password':
                case 'username':
                    unset($config[$key]);
                    break;
            }
        }
        return $config;
    }

    /**
     * Perform a recursive array diff
     *
     * Necessary starting in PHP 5.4; see https://bugs.php.net/bug.php?id=60278
     * 
     * @param  mixed $a 
     * @param  mixed $b 
     * @return int
     */
    public static function arrayDiffRecursive($a, $b)
    {
        if (is_array($a) && is_array($b)) {
            return array_diff_uassoc($a, $b, sprintf('%s::arrayDiffRecursive', __CLASS__));
        }
        if ($a === $b) {
            return 0;
        }
        return ($a > $b) ? 1 : -1;
    }

    /**
     * Determine the configuration key based on the entity
     * 
     * @param AuthenticationEntity $entity 
     * @return string
     */
    protected function getConfigKey(AuthenticationEntity $entity)
    {
        $key = 'zf-mvc-auth.authentication.';
        switch (true) {
            case $entity->isBasic():
            case $entity->isDigest():
                $key .= 'http';
                break;
            case $entity->isOAuth2():
                $key .= 'oauth2';
                break;
        }
        return $key;
    }

    /**
     * Fetch HTTP Authentication configuration
     * 
     * @param array $config 
     * @return array|false
     */
    protected function fetchHttpAuthConfiguration(array $config)
    {
        if (!isset($config['zf-mvc-auth']['authentication']['http']['accept_schemes'])
            || !is_array($config['zf-mvc-auth']['authentication']['http']['accept_schemes'])
        ) {
            return false;
        }

        $config = $config['zf-mvc-auth']['authentication']['http'];

        $localConfig = $this->localConfig->fetch(true);
        if (isset($localConfig['zf-mvc-auth']['authentication']['http'])
            && is_array($localConfig['zf-mvc-auth']['authentication']['http'])
        ) {
            $config = array_merge($config, $localConfig['zf-mvc-auth']['authentication']['http']);
        }

        return $config;
    }

    /**
     * Fetch all OAuth2 configuration from global and local files
     * 
     * @param array $config 
     * @return array|false
     */
    protected function fetchOAuth2Configuration(array $config)
    {
        $oauth2Config = array(
            'route_match' => '/oauth',
        );
        if (isset($config['router']['routes']['oauth']['options']['route'])) {
            $oauth2Config['route_match'] = $config['router']['routes']['oauth']['options']['route'];
        }

        $localConfig = $this->localConfig->fetch(true);
        if (!isset($localConfig['zf-mvc-auth']['authentication']['oauth2'])
            || !is_array($localConfig['zf-mvc-auth']['authentication']['oauth2'])
        ) {
            return false;
        }

        $oauth2Config = array_merge($oauth2Config, $localConfig['zf-mvc-auth']['authentication']['oauth2']);

        return $oauth2Config;
    }

    /**
     * Patch the HTTP Authentication configuration
     * 
     * @param AuthenticationEntity $entity 
     * @param array $global 
     * @param array $local 
     */
    protected function patchHttpAuthConfig(AuthenticationEntity $entity, array $global, array $local)
    {
        $key = $this->getConfigKey($entity);
        $this->globalConfig->patchKey($key, $global);
        $this->localConfig->patchKey($key, $local);
    }

    /**
     * Patch the OAuth2 configuration
     * 
     * @param AuthenticationEntity $entity 
     * @param array $global 
     * @param array $local 
     * @return void
     */
    protected function patchOAuth2Config(AuthenticationEntity $entity, array $global, array $local)
    {
        if (isset($global['route_match']) && $global['route_match']) {
            $this->globalConfig->patchKey('router.routes.oauth.options.route', $global['route_match']);
        }

        $key = $this->getConfigKey($entity);
        $this->localConfig->patchKey($key, $local);
    }
}
