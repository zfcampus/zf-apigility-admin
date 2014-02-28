<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use ZF\Apigility\Admin\Exception;

class AuthorizationEntity implements
    Countable,
    IteratorAggregate
{
    const TYPE_ENTITY     = 'entity';
    const TYPE_COLLECTION = 'collection';

    protected $allowedRestTypes = array(
        self::TYPE_ENTITY,
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

    public function count()
    {
        return count($this->servicePrivileges);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->servicePrivileges);
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

    public function addRestService($serviceName, $entityOrCollection, array $privileges = null)
    {
        if (!in_array($entityOrCollection, $this->allowedRestTypes)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid type "%s" provided for %s; must be one of "%s" or "%s"',
                $entityOrCollection,
                __METHOD__,
                self::TYPE_ENTITY,
                self::TYPE_COLLECTION
            ));
        }
        $this->addRpcService($serviceName, sprintf('__%s__', $entityOrCollection), $privileges);
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
