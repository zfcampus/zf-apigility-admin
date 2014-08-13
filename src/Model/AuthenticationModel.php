<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use PDO;
use PDOException;
use MongoConnectionException;
use ZF\Apigility\Admin\Exception;
use ZF\Configuration\ConfigResource;
use ZF\Rest\Exception\CreationException;

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
     * @param array $authenticationConfig
     * @return AuthenticationEntity
     * @throws \ZF\Rest\Exception\CreationException
     */
    public function create(array $authenticationConfig)
    {
        if ($this->fetch() instanceof AuthenticationEntity) {
            throw new CreationException('Authentication already exists', 409);
        }

        $entity  = $this->createAuthenticationEntityFromConfig($authenticationConfig);

        if ($entity->isOAuth2()) {
            $data = $entity->getArrayCopy();
            $dsnType = isset($data['dsn_type']) ? $data['dsn_type'] : AuthenticationEntity::DSN_PDO;
            $this->validateDsn($data['dsn'], $data['username'], $data['password'], $dsnType);
        }

        $allData = $entity->getArrayCopy();
        unset($allData['type']);
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

        if ($current->isOAuth2()) {
            $data = $current->getArrayCopy();
            $dsnType = isset($data['dsn_type']) ? $data['dsn_type'] : AuthenticationEntity::DSN_PDO;
            $this->validateDsn($data['dsn'], $data['username'], $data['password'], $dsnType);
        }

        $allData = $current->getArrayCopy();
        unset($allData['type']);
        $global  = $this->removeSensitiveConfig($allData);
        $local   = array_udiff_assoc($allData, $global, sprintf('%s::arrayDiffRecursive', __CLASS__));
        switch (true) {
            case $current->isBasic():
            case $current->isDigest():
                $this->patchHttpAuthConfig($current, $global, $local);
                break;
            case $current->isOAuth2():
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
            'zf-oauth2.db',
            'zf-oauth2.mongo',
            'zf-oauth2.storage',
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
            case (isset($config['dsn']) || isset($config['dsn_type'])):
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
                case 'dsn_type':
                case 'dsn':
                case 'database':
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
        if (isset($localConfig['zf-oauth2']['db'])
            && is_array($localConfig['zf-oauth2']['db'])
        ) {
            return array_merge($oauth2Config, $localConfig['zf-oauth2']['db']);;
        }

        if (isset($localConfig['zf-oauth2']['mongo'])
            && is_array($localConfig['zf-oauth2']['mongo'])
        ) {
            return array_merge($oauth2Config, $localConfig['zf-oauth2']['mongo']);;
        }



        return false;
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
        $key = 'zf-mvc-auth.authentication.http';
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

        switch ($entity->getDsnType()) {
            case AuthenticationEntity::DSN_MONGO:
                $toSet = array(
                    'storage' => 'ZF\OAuth2\Adapter\MongoAdapter',
                    'mongo'   => $local,
                );
                break;
            case AuthenticationEntity::DSN_PDO:
            default:
                $toSet = array(
                    'storage' => 'ZF\OAuth2\Adapter\PdoAdapter',
                    'db'      => $local,
                );
                break;
        }

        $key = 'zf-oauth2';
        $this->localConfig->patchKey($key, $toSet);
    }

    /**
     * Validate a DSN
     *
     * @param  string $dsnType
     * @param  string $dsn
     * @param  string $username
     * @param  string $password
     * @throws Exception\InvalidArgumentException on invalid DSN
     * @return boolean
     */
    protected function validateDsn($dsn, $username = null, $password = null, $dsnType = AuthenticationEntity::DSN_PDO)
    {
        try {
            $this->{'create' . ucfirst(strtolower($dsnType)) . 'DSN'}($dsn, $username, $password);
            return true;
        } catch (MongoConnectionException $mongoException) {
        } catch (PDOException $pdoException) {
        }

        throw new Exception\InvalidArgumentException(
            sprintf('Invalid DSN "%s" provided', $dsn), 422
        );
    }

    /**
     * @param $dsn
     * @return \MongoClient
     */
    protected function createMongoDSN($dsn)
    {
        return new \MongoClient($dsn);
    }

    /**
     * @param $dsn
     * @param $username
     * @param $password
     * @return PDO
     */
    protected function createPdoDsn($dsn, $username, $password)
    {
        return new PDO($dsn, $username, $password);
    }
}
