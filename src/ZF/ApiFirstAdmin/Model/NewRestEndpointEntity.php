<?php

namespace ZF\ApiFirstAdmin\Model;

use ZF\Rest\Exception\CreationException;

class NewRestEndpointEntity extends RestEndpointEntity
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
                    break;
            }
        }

        if (null === $this->resourceName) {
            throw new CreationException('No resource name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->identifierName) {
            $this->identifierName = sprintf(
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
