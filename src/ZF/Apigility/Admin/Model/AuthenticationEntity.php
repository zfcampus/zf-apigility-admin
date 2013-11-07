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

    protected $digestDomains = '/';
    protected $nonceTimeout = 3600;
    protected $realm;
    protected $type;
    protected $htdigest;
    protected $htpasswd;


    public function __construct($type = self::TYPE_BASIC, $realm = 'api', array $params = array())
    {
        $this->type = in_array($type, array(self::TYPE_BASIC, self::TYPE_DIGEST)) ? $type : self::TYPE_BASIC;
        $this->realm = $realm;
        $this->exchangeArray($params);
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
        }

        foreach ($array as $key => $value) {
            $key = strtolower(str_replace('_', '', $key));
            if (!in_array($key, $allowedKeys)) {
                continue;
            }
            switch ($key) {
                case 'realm':
                    $this->realm = $value;
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
}
