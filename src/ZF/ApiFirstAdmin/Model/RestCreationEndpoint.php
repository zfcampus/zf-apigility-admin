<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\Filter\FilterChain;
use ZF\Rest\Exception\CreationException;

class RestCreationEndpoint
{
    protected $filters = array();

    protected $resourceName;

    protected $identifierName;

    protected $route;

    protected $resourceHttpOptions = array('GET', 'PATCH', 'PUT', 'DELETE');

    protected $collectionHttpOptions = array('GET', 'POST');

    protected $collectionName;

    protected $collectionQueryWhitelist = array();

    protected $pageSize = 25;

    protected $pageSizeParam = 'page';

    protected $selector = 'HalJson';

    protected $acceptWhitelist = array();

    protected $contentTypeWhitelist = array();

    public function __get($name)
    {
        if (!isset($this->{$name})
            || $name === 'filter'
        ) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }
        return $this->{$name};
    }

    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'resourcename':
                    $this->resourceName = $value;
                    break;
                case 'identifiername':
                    $this->identifierName = $value;
                    break;
                case 'route':
                    $this->route = $value;
                    break;
                case 'resourcehttpoptions':
                    $this->resourceHttpOptions = $value;
                    break;
                case 'collectionhttpoptions':
                    $this->collectionHttpOptions = $value;
                    break;
                case 'collectionname':
                    $this->resourceHttpOptions = $value;
                    break;
                case 'collectionquerywhitelist':
                    $this->collectionQueryWhitelist = $value;
                    break;
                case 'pagesize':
                    $this->pageSize = $value;
                    break;
                case 'pagesizeparam':
                    $this->pageSizeParam = $value;
                    break;
                case 'selector':
                    $this->selector = $value;
                    break;
                case 'acceptwhitelist':
                    $this->acceptWhitelist = $value;
                    break;
                case 'contenttypewhitelist':
                    $this->contentTypeWhitelist = $value;
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

        if (null === $this->route) {
            $this->route = sprintf(
                '/%s/%s',
                $this->normalizeResourceNameForRoute($this->resourceName),
                $this->identifierName
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeResourceNameForIdentifier($this->resourceName);
        }
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
