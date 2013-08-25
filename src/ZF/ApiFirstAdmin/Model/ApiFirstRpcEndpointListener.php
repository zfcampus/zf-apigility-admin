<?php

namespace ZF\ApiFirstAdmin\Model;

use RuntimeException;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;

/**
 * @todo We need to create a factory for returning a CodeConnectedRpc object based on the module name 
 *       and the configuration resource.
 */
class ApiFirstModuleListener extends AbstractResourceListener
{
    protected $moduleName;

    protected $rpcFactory;

    public function __construct($rpcFactory)
    {
        $this->rpcFactory = $rpcFactory;
    }

    public function getModuleName()
    {
        if (null !== $this->moduleName) {
            return $this->moduleName;
        }

        $moduleName = $this->getEvent()->getRouteMatch('name', false);
        if (!$moduleName) {
            throw new RuntimeException(sprintf(
                '%s cannot operate correctly without a "name" segment in the route matches',
                __CLASS__
            ));
        }
        $this->moduleName = $moduleName;
        return $moduleName;
    }

    public function getModel()
    {
        if ($this->model instanceof CodeConnectedRpc) {
            return $this->model;
        }
        $moduleName = $this->getModuleName();
        $this->model = $this->rpcFactory->factory($moduleName);
        return $this->model;
    }

    /**
     * Create a new RPC endpoint
     *
     * @param  array|object $data
     * @return RpcEndpointMetadata
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $creationData = array();

        // Munge data:
        // - need service_name
        // - need route
        // - need http_methods (can default to array('GET'))
        // - need selector (can default to null)
        // ...

        try {
            $controllerServiceName = $this->getModel()->createService(
                $creationData['service_name'],
                $creationData['route'],
                $creationData['http_methods'],
                $creationData['selector']
            );
        } catch (\Exception $e) {
            throw new CreationException('Unable to create RPC endpoint', $e->getCode(), $e);
        }

        return $this->getModel()->fetch($controllerServiceName);
    }

    /**
     * Fetch RPC metadata
     *
     * @param  string $id
     * @return RpcEndpointMetadata|ApiProblem
     */
    public function fetch($id)
    {
        $endpoint = $this->getModel()->fetch($id);
        if (!$endpoint instanceof RpcEndpointMetadata) {
            return new ApiProblem(404, 'RPC endpoint not found');
        }
        return $endpoint;
    }

    /**
     * Fetch metadata for all RPC endpoints
     *
     * @param  array $params
     * @return RpcEndpointMetadata[]
     */
    public function fetchAll($params = array())
    {
        return $this->getModel()->fetchAll();
    }

    /**
     * @todo Implement!
     */
    public function patch($id, $data)
    {
    }
}
