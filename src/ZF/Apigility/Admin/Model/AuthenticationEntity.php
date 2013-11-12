<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

class AuthenticationEntity
{
    const TYPE_BASIC  = 'basic';
    const TYPE_DIGEST = 'digest';
    const TYPE_OAUTH2 = 'oauth2';

    /**
     * Digest domains for HTTP digest authentication (space-separated list of paths)
     * 
     * @var string
     */
    protected $digestDomains = '/';

    /**
     * PDO DSN of database for use with zf-oauth2
     * 
     * @var string
     */
    protected $dsn;

    /**
     * Path to file containing HTTP digest credentials
     * 
     * @var string
     */
    protected $htdigest;

    /**
     * Path to file containing HTTP basic credentials
     * 
     * @var string
     */
    protected $htpasswd;

    /**
     * Nonce timeout for HTTP digest authentication
     * 
     * @var int
     */
    protected $nonceTimeout = 3600;

    /**
     * Database password for zf-oauth2
     * 
     * @var string
     */
    protected $password;

    /**
     * Realm to use with either HTTP basic or digest authentication
     * 
     * @var string
     */
    protected $realm;

    /**
     * Literal URI path to match for OAuth2 authentication endpoint
     * 
     * @var string
     */
    protected $routeMatch;

    /**
     * Authentication type
     * 
     * @var string
     */
    protected $type;

    /**
     * Database username for zf-oauth2
     * 
     * @var string
     */
    protected $username;


    public function __construct($type = self::TYPE_BASIC, $realmOrParams = 'api', array $params = array())
    {
        $this->type = in_array($type, array(self::TYPE_BASIC, self::TYPE_DIGEST, self::TYPE_OAUTH2)) ? $type : self::TYPE_BASIC;

        if ($type === self::TYPE_OAUTH2
            && is_array($realmOrParams)
        ) {
            $this->exchangeArray($realmOrParams);
        } else {
            $this->realm = $realmOrParams;
            $this->exchangeArray($params);
        }
    }

    public function getArrayCopy()
    {
        switch ($this->type) {
            case self::TYPE_BASIC:
                return array(
                    'accept_schemes' => array(self::TYPE_BASIC),
                    'realm'          => $this->realm,
                    'htpasswd'       => $this->htpasswd,
                );
            case self::TYPE_DIGEST:
                return array(
                    'accept_schemes' => array(self::TYPE_DIGEST),
                    'realm'          => $this->realm,
                    'htdigest'       => $this->htdigest,
                    'digest_domains' => $this->digestDomains,
                    'nonce_timeout'  => $this->nonceTimeout,
                );
            case self::TYPE_OAUTH2:
                return array(
                    'dsn'         => $this->dsn,
                    'username'    => $this->username,
                    'password'    => $this->password,
                    'route_match' => $this->routeMatch,
                );
        }
    }

    public function exchangeArray(array $array)
    {
        switch ($this->type) {
            case self::TYPE_BASIC:
                $allowedKeys = array('realm', 'htpasswd');
                break;
            case self::TYPE_DIGEST:
                $allowedKeys = array('realm', 'htdigest', 'digestdomains', 'noncetimeout');
                break;
            case self::TYPE_OAUTH2:
                $allowedKeys = array('dsn', 'username', 'password', 'routematch');
                break;
        }

        foreach ($array as $key => $value) {
            $key = strtolower(str_replace('_', '', $key));
            if (!in_array($key, $allowedKeys)) {
                continue;
            }
            switch ($key) {
                case 'dsn':
                    $this->dsn = $value;
                    break;
                case 'htdigest':
                    $this->htdigest = $value;
                    break;
                case 'htpasswd':
                    $this->htpasswd = $value;
                    break;
                case 'digestdomains':
                    $this->digestDomains = $value;
                    break;
                case 'noncetimeout':
                    $this->nonceTimeout = $value;
                    break;
                case 'password':
                    $this->password = $value;
                    break;
                case 'realm':
                    $this->realm = $value;
                    break;
                case 'routematch':
                    $this->routeMatch = $value;
                    break;
                case 'username':
                    $this->username = $value;
                    break;
            }
        }
    }

    public function isBasic()
    {
        return ($this->type === self::TYPE_BASIC);
    }

    public function isDigest()
    {
        return ($this->type === self::TYPE_DIGEST);
    }

    public function isOAuth2()
    {
        return ($this->type === self::TYPE_OAUTH2);
    }
}
