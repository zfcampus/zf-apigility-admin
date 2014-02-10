<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Rest\Exception\CreationException;

class NewRestServiceEntity extends RestServiceEntity
{
    protected $resourceName;

    public function exchangeArray(array $data)
    {
        parent::exchangeArray($data);
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'resourcename':
                    $this->resourceName = $value;
                    $this->serviceName  = $value;
                    break;
            }
        }

        if (null === $this->resourceName) {
            throw new CreationException('No resource name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->routeIdentifierName) {
            $this->routeIdentifierName = sprintf(
                '%s_id',
                $this->normalizeResourceNameForIdentifier($this->resourceName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeResourceNameForRoute($this->resourceName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeResourceNameForIdentifier($this->resourceName);
        }
    }

    public function getArrayCopy()
    {
        $return = parent::getArrayCopy();
        $return['resource_name'] = $this->resourceName;
        return $return;
    }
}
