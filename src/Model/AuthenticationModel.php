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
use ZF\Apigility\Admin\InputFilter\Authentication;

class AuthenticationModel
{

    const ADAPTER_HTTP   = 'ZF\\MvcAuth\\Authentication\\HttpAdapter';
    const ADAPTER_OAUTH2 = 'ZF\\MvcAuth\\Authentication\\OAuth2Adapter';

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
     * Create authentication configuration for version 2
     *
     * @param  array $authenticationConfig
     * @return array
     */
    public function createVersion2(array $adapter)
    {
        $config = $this->localConfig->fetch(true);

        $result = $this->checkInputDataVersion2($adapter);

        if (isset($config['zf-mvc-auth']['authentication']['adapters'][$result['name']])) {
            throw new CreationException('Authentication already exists', 409);
        } else {
            $config = $this->globalConfig->fetch(true);
            if (isset($config['zf-mvc-auth']['authentication']['adapters'][$result['name']])) {
                throw new CreationException('Authentication already exists', 409);
            }
        }

        if (!$this->saveAuthentication2($result)) {
            throw new Exception\RuntimeException(
                'Error saving the authentication data in config file',
                500
            );
        }
        return $result;
    }

    /**
     * Update authentication configuration for version 2
     *
     * @param  array $authenticationConfig
     * @return array
     */
    public function updateVersion2($name, array $adapter)
    {
        $config = $this->localConfig->fetch(true);
        if (isset($adapter['name'])) {
            $adapter['name'] = $name;
        }
        if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
                throw new Exception\RuntimeException(
                    'The authentication adapter specified doesn\'t exist',
                    404
                );
            }
        }
        $result = $this->checkInputDataVersion2($adapter);

        if (!$this->saveAuthentication2($result)) {
            throw new Exception\RuntimeException(
                'Error saving the authentication data in config file',
                500
            );
        }
        return $result;
    }

    /**
     * Check and return the input data for version 2
     *
     * @param  array $adapter
     * @return array
     * @throws Exception\InvalidArgumentException
     */
    protected function checkInputDataVersion2(array $adapter)
    {
        switch (strtolower($adapter['type'])) {
            case AuthenticationEntity::TYPE_BASIC:
                $filter = new Authentication\BasicInputFilter2();
                break;
            case AuthenticationEntity::TYPE_DIGEST:
                $filter = new Authentication\DigestInputFilter2();
                break;
            case AuthenticationEntity::TYPE_OAUTH2:
                if (!isset($adapter['oauth2_type'])) {
                    throw new Exception\InvalidArgumentException(
                        'OAuth2 type missing',
                        422
                    );
                }
                switch(strtolower($adapter['oauth2_type'])) {
                    case strtolower(AuthenticationEntity::DSN_PDO):
                        $filter = new Authentication\OAuth2PdoInputFilter2();
                        break;
                    case strtolower(AuthenticationEntity::DSN_MONGO):
                        $filter = new Authentication\OAuth2MongoInputFilter2();
                        break;
                    default:
                        throw new Exception\InvalidArgumentException(
                            'Invalid OAuth2 type specified',
                            422
                        );
                }
                break;
            default:
                throw new Exception\InvalidArgumentException(
                    'Authentication type missing or not valid',
                    422
                );
        }
        $filter->init();
        $filter->setData($adapter);

        if (!$filter->isValid()) {
            $msg = $filter->getMessages();
            $field = key($msg);
            throw new Exception\InvalidArgumentException(
                $msg[$field][0],
                422
            );
        }

        $result = $filter->getValues();
        if (AuthenticationEntity::TYPE_OAUTH2 === $result['type']) {
            $username = isset($result['oauth2_username']) ? $result['oauth2_username'] : null;
            $password = isset($result['oauth2_password']) ? $result['oauth2_password'] : null;
            $this->validateDsn($result['oauth2_dsn'], $username, $password, $result['oauth2_type']);
        }
        return $result;
    }

    /**
     * Remove the authentication adapter specified
     *
     * @param  string $name
     * @return boolen
     */
    public function removeVersion2($name)
    {
        $config = $this->localConfig->fetch(true);
        $key    = 'zf-mvc-auth.authentication.adapters.' . $name;

        if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
                throw new Exception\RuntimeException(
                    'The authentication adapter specified doesn\'t exist',
                    404
                );
            }
            $this->globalConfig->deleteKey($key);
        } else {
            $this->localConfig->deleteKey($key);
        }
        return true;
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
     * Fetch configuration details for specific auth adapter name
     * Used since Apigility 1.1
     *
     * @param  string $name
     * @return array
     */
    public function fetchByName($name)
    {
        $config = $this->localConfig->fetch(true);
        if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
            $config = $this->globalConfig->fetch(true);
            if (!isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
                return false;
            }
        }
        return $this->loadAuthVer2FromConfig($name, $config);
    }

    /**
     * Fetch configuration details for auth adapters
     * Used since Apigility 1.1
     *
     * @return array
     */
    public function fetchAll()
    {
        $result = array();
        $config = $this->localConfig->fetch(true);
        if (!isset($config['zf-mvc-auth']['authentication']['adapters'])) {
            $config = $this->globalConfig->fetch(true);
            if (!isset($config['zf-mvc-auth']['authentication']['adapters'])) {
                return $result;
            }
        }
        foreach ($config['zf-mvc-auth']['authentication']['adapters'] as $name => $adapter) {
          $result[] = $this->loadAuthVer2FromConfig($name, $config);
        }
        return $result;
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
            return array_merge($oauth2Config, $localConfig['zf-oauth2']['db']);
        }

        if (isset($localConfig['zf-oauth2']['mongo'])
            && is_array($localConfig['zf-oauth2']['mongo'])
        ) {
            return array_merge($oauth2Config, $localConfig['zf-oauth2']['mongo']);
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
            sprintf('Invalid DSN "%s" provided', $dsn),
            422
        );
    }

    /**
     * @param  $dsn
     * @return \MongoClient
     */
    protected function createMongoDSN($dsn)
    {
        return new \MongoClient($dsn);
    }

    /**
     * @param  $dsn
     * @param  $username
     * @param  $password
     * @return PDO
     */
    protected function createPdoDSN($dsn, $username, $password)
    {
        return new PDO($dsn, $username, $password);
    }

    /**
     * Add a new authentication adapter in local config
     */
    protected function saveAuthentication2(array $adapter)
    {
        $key = 'zf-mvc-auth.authentication.adapters.' . $adapter['name'];
        switch ($adapter['type']) {
            case AuthenticationEntity::TYPE_BASIC:
                $config = array(
                    'adapter' => self::ADAPTER_HTTP,
                    'options' => array(
                        'accept_schemes' => array(AuthenticationEntity::TYPE_BASIC),
                        'realm'          => $adapter['realm'],
                        'htpasswd'       => $adapter['htpasswd']
                    )
                );
                break;
            case AuthenticationEntity::TYPE_DIGEST:
                $config = array(
                    'adapter' => self::ADAPTER_HTTP,
                    'options' => array(
                        'accept_schemes' => array(AuthenticationEntity::TYPE_DIGEST),
                        'realm'          => $adapter['realm'],
                        'digest_domains' => $adapter['digest_domains'],
                        'nonce_timeout'  => $adapter['nonce_timeout'],
                        'htdigest'       => $adapter['htdigest']
                    )
                );
                break;
            case AuthenticationEntity::TYPE_OAUTH2:
                switch($adapter['oauth2_type']) {
                    case strtolower(AuthenticationEntity::DSN_PDO):
                        $config = array(
                            'adapter' => self::ADAPTER_OAUTH2,
                            'storage' => array(
                                'adapter'  => strtolower(AuthenticationEntity::DSN_PDO),
                                'dsn'      => $adapter['oauth2_dsn']
                            )
                        );
                        if (isset($adapter['oauth2_username'])) {
                          $config['storage']['oauth2_username'] = $adapter['oauth2_username'];
                        }
                        if (isset($adapter['oauth2_password'])) {
                          $config['storage']['oauth2_password'] = $adapter['oauth2_password'];
                        }
                        break;
                    case strtolower(AuthenticationEntity::DSN_MONGO):
                        $config = array(
                            'adapter' => self::ADAPTER_OAUTH2,
                            'storage' => array(
                                'adapter'  => strtolower(AuthenticationEntity::DSN_MONGO),
                                'dsn'      => $adapter['oauth2_dsn'],
                                'database' => $adapter['oauth2_database']
                            )
                        );
                        if (isset($adapter['oauth2_locator_name'])) {
                            $config['storage']['locator_name'] = $adapter['oauth2_locator_name'];
                        }
                        break;
                }
                if (isset($adapter['oauth2_options'])) {
                    $config['storage']['options'] = $adapter['oauth2_options'];
                }
                break;
        }

        $oldConfig = $this->localConfig->fetch(true);
        if (isset($oldConfig['zf-mvc-auth']['authentication']['adapters'][$adapter['name']])) {
            $this->localConfig->patchKey($key, $config);
        } else {
          $oldConfig = $this->globalConfig->fetch(true);
          if (isset($oldConfig['zf-mvc-auth']['authentication']['adapters'][$adapter['name']])) {
              $this->globalConfig->patchKey($key, $config);
          } else {
              $this->localConfig->patchKey($key, $config);
          }
        }
        return true;
    }

    /**
     * Load authentication data from configuration version 2
     * Since Apigility 1.1
     *
     * @param  string $name
     * @param  array $config
     * @return array
     */
    protected function loadAuthVer2FromConfig($name, array $config)
    {
        $result = array();
        if (isset($config['zf-mvc-auth']['authentication']['adapters'][$name])) {
            $adapter = $config['zf-mvc-auth']['authentication']['adapters'][$name];
            $result['name'] = $name;
            switch ($adapter['adapter']) {
                case self::ADAPTER_HTTP:
                    $result['type'] = array_shift($adapter['options']['accept_schemes']);
                    switch ($result['type']) {
                        case AuthenticationEntity::TYPE_BASIC:
                            $result['realm'] = $adapter['options']['realm'];
                            $result['htpasswd'] = $adapter['options']['htpasswd'];
                            break;
                        case AuthenticationEntity::TYPE_DIGEST:
                            $result['realm'] = $adapter['options']['realm'];
                            $result['digest_domains'] = $adapter['options']['digest_domains'];
                            $result['nonce_timeout'] = $adapter['options']['nonce_timeout'];
                            $result['htdigest'] = $adapter['options']['htdigest'];
                            break;
                    }
                    break;
                case self::ADAPTER_OAUTH2:
                    $result['type'] = 'oauth2';
                    $result['oauth2_type'] = $adapter['storage']['adapter'];
                    $result['oauth2_dsn'] = $adapter['storage']['dsn'];
                    if (isset($adapter['storage']['options'])) {
                        $result['oauth2_options'] = $adapter['storage']['options'];
                    }
                    switch ($result['oauth2_type']) {
                        case AuthenticationEntity::DSN_PDO:
                            $result['oauth2_username'] = $adapter['storage']['username'];
                            $result['oauth2_password'] = $adapter['storage']['password'];
                            break;
                        case AuthenticationEntity::DSN_MONGO:
                            $result['oauth2_database'] = $adapter['storage']['database'];
                            if (isset($adapter['storage']['locator_name'])) {
                                $result['oauth2_locator_name'] = $adapter['storage']['locator_name'];
                            }
                            break;
                    }
                    break;
            }

        }
        return $result;
    }
}
