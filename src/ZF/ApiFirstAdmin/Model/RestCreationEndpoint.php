<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\Filter\FilterChain;
use ZF\Rest\Exception\CreationException;

class RestCreationEndpoint extends RestEndpointMetadata
{
    protected $filters = array();

    protected $resourceName;

    public function __get($name)
    {
        if ($name === 'filter') {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }
        return parent::__get($name);
    }

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

    protected function normalizeResourceNameForIdentifier($resourceName)
    {
        return $this->getIdentifierNormalizationFilter()->filter($resourceName);
    }

    protected function normalizeResourceNameForRoute($resourceName)
    {
        return $this->getRouteNormalizationFilter()->filter($resourceName);
    }

    /**
     * Retrieve and/or initialize the normalization filter chain for identifiers
     *
     * @return FilterChain
     */
    protected function getIdentifierNormalizationFilter()
    {
        if (isset($this->filters['identifier'])
            && $this->filters['identifier'] instanceof FilterChain
        ) {
            return $this->filters['identifier'];
        }
        $filter = new FilterChain();
        $filter->attachByName('WordCamelCaseToUnderscore')
               ->attachByName('StringToLower');
        $this->filters['identifier'] = $filter;
        return $filter;
    }

    /**
     * Retrieve and/or initialize the normalization filter chain
     *
     * @return FilterChain
     */
    protected function getRouteNormalizationFilter()
    {
        if (isset($this->filters['route'])
            && $this->filters['route'] instanceof FilterChain
        ) {
            return $this->filters['route'];
        }
        $filter = new FilterChain();
        $filter->attachByName('WordCamelCaseToDash')
               ->attachByName('StringToLower');
        $this->filters['route'] = $filter;
        return $filter;
    }
}
