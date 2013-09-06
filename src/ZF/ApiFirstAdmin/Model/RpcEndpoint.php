<?php

namespace ZF\ApiFirstAdmin\Model;

use InvalidArgumentException;
use RuntimeException;

class RpcEndpoint
{
    protected $acceptWhitelist = array();

    protected $contentTypeWhitelist = array();

    protected $controllerServiceName;

    protected $httpMethods = array('GET');

    protected $routeMatch;

    protected $routeName;

    protected $selector = 'Json';

    /**
     * @todo   validation
     * @param  array $data
     * @throws InvalidArgumentException if a particular value does not validate
     * @throws RuntimeException if the object does not have a controller service name following population
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);

            switch ($key) {
                case 'acceptwhitelist':
                case 'accept_whitelist':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(sprintf(
                            '%s expects an array value for "%s"; received "%s"',
                            __CLASS__,
                            (is_object($value) ? get_class($value) : gettype($value))
                        ));
                    }
                    $this->acceptWhitelist = $value;
                    break;
                case 'contenttypewhitelist':
                case 'content_type_whitelist':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(sprintf(
                            '%s expects an array value for "%s"; received "%s"',
                            __CLASS__,
                            (is_object($value) ? get_class($value) : gettype($value))
                        ));
                    }
                    $this->contentTypeWhitelist = $value;
                    break;
                case 'controllerservicename':
                case 'controller_service_name':
                    $this->controllerServiceName = $value;
                    break;
                case 'httpoptionss':
                case 'http_options':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(sprintf(
                            '%s expects an array value for "%s"; received "%s"',
                            __CLASS__,
                            (is_object($value) ? get_class($value) : gettype($value))
                        ));
                    }
                    $this->httpMethods = $value;
                    break;
                case 'routematch':
                case 'route_match':
                    $this->routeMatch = $value;
                    break;
                case 'routename':
                case 'route_name':
                    $this->routeName = $value;
                    break;
                case 'selector':
                    $this->selector = $value;
                    break;
                default:
                    break;
            }
        }

        if (null === $this->controllerServiceName
            || !is_string($this->controllerServiceName)
            || empty($this->controllerServiceName)
        ) {
            throw new RuntimeException(sprintf(
                '%s requires a controller service name; none present following population',
                __CLASS__
            ));
        }
    }

    /**
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'accept_whitelist'        => $this->acceptWhitelist,
            'content_type_whitelist'  => $this->contentTypeWhitelist,
            'controller_service_name' => $this->controllerServiceName,
            'http_options'            => $this->httpMethods,
            'route_match'             => $this->routeMatch,
            'route_name'              => $this->routeName,
            'selector'                => $this->selector,
        );
    }
}
