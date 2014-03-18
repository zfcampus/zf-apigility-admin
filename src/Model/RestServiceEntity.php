<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\FilterChain;
use ZF\Hal\Collection as HalCollection;

class RestServiceEntity
{
    protected $acceptWhitelist = array(
        'application/json',
        'application/*+json',
    );

    protected $collectionClass;

    protected $collectionHttpMethods = array('GET', 'POST');

    protected $collectionName;

    protected $collectionQueryWhitelist = array();

    protected $contentTypeWhitelist = array(
        'application/json',
    );

    protected $controllerServiceName;

    protected $documentation;

    protected $entityClass;

    protected $entityHttpMethods = array('GET', 'PATCH', 'PUT', 'DELETE');

    protected $entityIdentifierName = 'id';

    protected $filters = array();

    protected $hydratorName = 'Zend\Stdlib\Hydrator\ArraySerializable';

    protected $inputFilters;

    protected $module;

    protected $pageSize = 25;

    protected $pageSizeParam;

    protected $resourceClass;

    protected $routeIdentifierName;

    protected $routeMatch;

    protected $routeName;

    protected $selector = 'HalJson';

    protected $serviceName;

    public function __get($name)
    {
        if ($name === 'filter') {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        /**
         * @todo Remove this prior to 1.0; BC fix implemented prior to 0.9.0
         */
        if ($name === 'resourceHttpMethods') {
            $name = 'entityHttpMethods';
        }

        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }
        return $this->{$name};
    }

    public function __isset($name)
    {
        if ($name === 'filter') {
            return false;
        }

        /**
         * @todo Remove this prior to 1.0; BC fix implemented prior to 0.9.0
         */
        if ($name === 'resourceHttpMethods') {
            $name = 'entityHttpMethods';
        }

        return (property_exists($this, $name));
    }

    public function exchangeArray(array $data)
    {
        $legacyIdentifierName = false;
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'acceptwhitelist':
                    $this->acceptWhitelist = $value;
                    break;
                case 'collectionclass':
                    $this->collectionClass = $value;
                    break;
                case 'collectionhttpmethods':
                    $this->collectionHttpMethods = $value;
                    break;
                case 'collectionname':
                    $this->collectionName = $value;
                    break;
                case 'collectionquerywhitelist':
                    $this->collectionQueryWhitelist = $value;
                    break;
                case 'contenttypewhitelist':
                    $this->contentTypeWhitelist = $value;
                    break;
                case 'controllerservicename':
                    $this->controllerServiceName = $value;
                    break;
                case 'entityclass':
                    $this->entityClass = $value;
                    break;
                case 'entityhttpmethods':
                    $this->entityHttpMethods = $value;
                    break;
                case 'entityidentifiername':
                    $this->entityIdentifierName = $value;
                    break;
                case 'hydratorname':
                    $this->hydratorName = $value;
                    break;
                case 'identifiername':
                    $legacyIdentifierName = $value;
                    break;
                case 'inputfilters':
                    if ($value instanceof InputFilterCollection
                        || $value instanceof HalCollection
                    ) {
                        $this->inputFilters = $value;
                    }
                    break;
                case 'documentation':
                    $this->documentation = $value;
                    break;
                case 'module':
                    $this->module = $value;
                    break;
                case 'pagesize':
                    $this->pageSize = $value;
                    break;
                case 'pagesizeparam':
                    $this->pageSizeParam = $value;
                    break;
                case 'resourceclass':
                    $this->resourceClass = $value;
                    break;
                case 'resourcehttpmethods':
                    $this->entityHttpMethods = $value;
                    break;
                case 'routeidentifiername':
                    $this->routeIdentifierName = $value;
                    break;
                case 'routematch':
                    $this->routeMatch = $value;
                    break;
                case 'routename':
                    $this->routeName = $value;
                    break;
                case 'selector':
                    $this->selector = $value;
                    break;
                case 'servicename':
                    $this->serviceName = $value;
                    break;
            }
        }

        if ($legacyIdentifierName && ! $this->routeIdentifierName) {
            $this->routeIdentifierName = $legacyIdentifierName;
        }

        if ($legacyIdentifierName && ! $this->entityIdentifierName) {
            $this->entityIdentifierName = $legacyIdentifierName;
        }
    }

    public function getArrayCopy()
    {
        $array = array(
            'accept_whitelist'           => $this->acceptWhitelist,
            'collection_class'           => $this->collectionClass,
            'collection_http_methods'    => $this->collectionHttpMethods,
            'collection_name'            => $this->collectionName,
            'collection_query_whitelist' => $this->collectionQueryWhitelist,
            'content_type_whitelist'     => $this->contentTypeWhitelist,
            'controller_service_name'    => $this->controllerServiceName,
            'entity_class'               => $this->entityClass,
            'entity_http_methods'        => $this->entityHttpMethods,
            'entity_identifier_name'     => $this->entityIdentifierName,
            'hydrator_name'              => $this->hydratorName,
            'module'                     => $this->module,
            'page_size_param'            => $this->pageSizeParam,
            'page_size'                  => $this->pageSize,
            'resource_class'             => $this->resourceClass,
            'route_identifier_name'      => $this->routeIdentifierName,
            'route_match'                => $this->routeMatch,
            'route_name'                 => $this->routeName,
            'selector'                   => $this->selector,
            'service_name'               => $this->serviceName,
        );
        if (null !== $this->inputFilters) {
            $array['input_filters'] = $this->inputFilters;
        }
        if (null !== $this->documentation) {
            $array['documentation'] = $this->documentation;
        }
        return $array;
    }

    protected function normalizeServiceNameForIdentifier($serviceName)
    {
        return $this->getIdentifierNormalizationFilter()->filter($serviceName);
    }

    protected function normalizeServiceNameForRoute($serviceName)
    {
        return $this->getRouteNormalizationFilter()->filter($serviceName);
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
