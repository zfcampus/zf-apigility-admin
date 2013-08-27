<?php

namespace ZF\ApiFirstAdmin\Model;

use RuntimeException;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;
use ZF\Rest\Exception\PatchException;

/**
 * @todo We need to create a factory for returning a CodeConnectedRpc object based on the module name 
 *       and the configuration resource.
 */
class ApiFirstRpcEndpointListener extends AbstractResourceListener
{
    /**
     * @var CodeConnectedRpc
     */
    protected $model;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var CodeConnectedRpcFactory
     */
    protected $rpcFactory;

    /**
     * @param  CodeConnectedRpcFactory $rpcFactory 
     */
    public function __construct(CodeConnectedRpcFactory $rpcFactory)
    {
        $this->rpcFactory = $rpcFactory;
    }

    /**
     * @return string
     * @throws RuntimeException if module name is not present in route matches
     */
    public function getModuleName()
    {
        if (null !== $this->moduleName) {
            return $this->moduleName;
        }

        $moduleName = $this->getEvent()->getRouteParam('name', false);
        if (!$moduleName) {
            throw new RuntimeException(sprintf(
                '%s cannot operate correctly without a "name" segment in the route matches',
                __CLASS__
            ));
        }
        $this->moduleName = $moduleName;
        return $moduleName;
    }

    /**
     * @return CodeConnectedRpc
     */
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
        // - need http_options (can default to array('GET'))
        // - need selector (can default to null)
        // ...

        try {
            $controllerServiceName = $this->getModel()->createService(
                $creationData['service_name'],
                $creationData['route'],
                $creationData['http_options'],
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
     * Update an existing RPC endpoint
     * 
     * @param  string $id 
     * @param  object|array $data 
     * @return ApiProblem|RpcEndpointMetadata
     * @throws PatchException if unable to update configuration
     */
    public function patch($id, $data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!is_array($data)) {
            return new ApiProblem(400, 'Invalid data provided for update');
        }

        if (empty($data)) {
            return new ApiProblem(400, 'No data provided for update');
        }

        $model = $this->getModel();
        foreach ($data as $key => $value) {
            try {
                switch (strtolower($key)) {
                    case 'httpoptions':
                    case 'http_options':
                        $model->updateHttpMethods($id, $value);
                        break;
                    case 'routematch':
                    case 'route_match':
                        $model->updateRoute($id, $value);
                        break;
                    case 'selector':
                        $model->updateSelector($id, $value);
                        break;
                    default:
                        break;
                }
            } catch (\Exception $e) {
                throw new PatchException('Error updating RPC endpoint', 500, $e);
            }
        }

        return $model->fetch($id);
    }
}
