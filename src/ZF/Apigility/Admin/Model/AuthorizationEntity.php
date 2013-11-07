<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ArrayIterator;
use IteratorAggregate;
use ZF\Apigility\Admin\Exception;

class AuthorizationEntity implements IteratorAggregate
{
    const TYPE_RESOURCE   = 'resource';
    const TYPE_COLLECTION = 'collection';

    protected $allowedRestTypes = array(
        self::TYPE_RESOURCE,
        self::TYPE_COLLECTION,
    );

    protected $defaultPrivileges = array(
        'GET'    => false,
        'POST'   => false,
        'PATCH'  => false,
        'PUT'    => false,
        'DELETE' => false,
    );

    protected $servicePrivileges = array();

    public function __construct(array $services = array())
    {
        foreach ($services as $serviceName => $privileges) {
            $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        }
    }

    public function getArrayCopy()
    {
        return $this->servicePrivileges;
    }

    public function exchangeArray(array $services)
    {
        foreach ($services as $serviceName => $privileges) {
            $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        }
    }

    public function getIterator()
    {
        return new ArrayIterator($this->servicePrivileges);
    }

    public function addRestService($serviceName, $resourceOrCollection, array $privileges = null)
    {
        if (!in_array($resourceOrCollection, $this->allowedRestTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided for %s; must be one of "%s" or "%s"',
                $resourceOrCollection,
                __METHOD__,
                self::TYPE_RESOURCE,
                self::TYPE_COLLECTION
            ));
        }
        $this->addRpcService($serviceName, sprintf('__%s__', $resourceOrCollection), $privileges);
        return $this;
    }

    public function addRpcService($serviceName, $action, array $privileges = null)
    {
        if (null === $privileges) {
            $privileges = $this->defaultPrivileges;
        }

        $serviceName = sprintf('%s::%s', $serviceName, $action);
        $this->servicePrivileges[$serviceName] = $this->filterPrivileges($privileges);
        return $this;
    }

    public function has($serviceName)
    {
        return array_key_exists($serviceName, $this->servicePrivileges);
    }

    public function get($serviceName)
    {
        if (!$this->has($serviceName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'No service by the name of "%s" has been registered',
                $serviceName
            ));
        }
        return $this->servicePrivileges[$serviceName];
    }

    protected function filterPrivileges(array $privileges)
    {
        foreach ($privileges as $httpMethod => $flag) {
            if (!array_key_exists($httpMethod, $this->defaultPrivileges)) {
                unset($privileges[$httpMethod]);
                continue;
            }
            if (!is_bool($flag)) {
                $privileges[$httpMethod] = (bool) $flag;
                continue;
            }
        }
        return $privileges;
    }
}
