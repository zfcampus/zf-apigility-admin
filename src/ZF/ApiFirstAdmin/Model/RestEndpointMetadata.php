<?php

namespace ZF\ApiFirstAdmin\Model;

class RestEndpointMetadata
{
    protected $acceptWhitelist = array();

    protected $collectionClass;

    protected $collectionHttpOptions = array('GET', 'POST');

    protected $collectionName;

    protected $collectionQueryWhitelist = array();

    protected $contentTypeWhitelist = array();

    protected $controllerServiceName;

    protected $entityClass;

    protected $identifierName;

    protected $module;

    protected $pageSize = 25;

    protected $pageSizeParam = 'page';

    protected $resourceClass;

    protected $resourceHttpOptions = array('GET', 'PATCH', 'PUT', 'DELETE');

    protected $routeName;

    protected $selector = 'HalJson';

    public function __get($name)
    {
        if (!isset($this->{$name})) {
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
                case 'acceptwhitelist':
                    $this->acceptWhitelist = $value;
                    break;
                case 'collectionclass':
                    $this->collectionClass = $value;
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
                case 'contenttypewhitelist':
                    $this->contentTypeWhitelist = $value;
                    break;
                case 'controllerservicename':
                    $this->controllerServiceName = $value;
                    break;
                case 'entityclass':
                    $this->entityClass = $value;
                    break;
                case 'identifiername':
                    $this->identifierName = $value;
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
                case 'resourcehttpoptions':
                    $this->resourceHttpOptions = $value;
                    break;
                case 'routeName':
                    $this->routeName = $value;
                    break;
                case 'selector':
                    $this->selector = $value;
                    break;
            }
        }
    }

    public function getArrayCopy()
    {
        return array(
            'accept_whitelist'           => $this->acceptWhitelist,
            'collection_class'           => $this->collectionClass,
            'collection_http_options'    => $this->collectionHttpOptions,
            'collection_name'            => $this->collectionName,
            'collection_query_whitelist' => $this->collectionQueryWhitelist,
            'content_type_whitelist'     => $this->contentTypeWhitelist,
            'controller_service_name'    => $this->controllerServiceName,
            'entity_class'               => $this->entityClass,
            'identifier_name'            => $this->identifierName,
            'module'                     => $this->module,
            'page_size'                  => $this->pageSize,
            'page_size_param'            => $this->pageSizeParam,
            'resource_class'             => $this->resourceClass,
            'resource_http_options'      => $this->resourceHttpOptions,
            'route_name'                 => $this->routeName,
            'selector'                   => $this->selector,
        );
    }
}
