<?php

namespace ZF\ApiFirstAdmin\Model;

use RuntimeException;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;
use ZF\Rest\Exception\PatchException;

class RestEndpointResource extends AbstractResourceListener
{
    /**
     * @var CodeConnectedRest
     */
    protected $model;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var CodeConnectedRestFactory
     */
    protected $restFactory;

    /**
     * @param  CodeConnectedRestFactory $restFactory
     */
    public function __construct(CodeConnectedRestFactory $restFactory)
    {
        $this->restFactory = $restFactory;
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
     * @return CodeConnectedRest
     */
    public function getModel()
    {
        if ($this->model instanceof CodeConnectedRest) {
            return $this->model;
        }
        $moduleName = $this->getModuleName();
        $this->model = $this->restFactory->factory($moduleName);
        return $this->model;
    }

    /**
     * Create a new REST endpoint
     *
     * @param  array|object $data
     * @return RestEndpoint
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $model        = $this->getModel();
        $creationData = new NewRestEndpoint();
        $creationData->exchangeArray($data);

        try {
            $endpoint = $model->createService($creationData);
        } catch (\Exception $e) {
            throw new CreationException('Unable to create REST endpoint', $e->getCode(), $e);
        }

        return $endpoint;
    }

    /**
     * Fetch REST metadata
     *
     * @param  string $id
     * @return RestEndpoint|ApiProblem
     */
    public function fetch($id)
    {
        $endpoint = $this->getModel()->fetch($id);
        if (!$endpoint instanceof RestEndpoint) {
            return new ApiProblem(404, 'REST endpoint not found');
        }
        return $endpoint;
    }

    /**
     * Fetch metadata for all REST endpoints
     *
     * @param  array $params
     * @return RestEndpoint[]
     */
    public function fetchAll($params = array())
    {
        return $this->getModel()->fetchAll();
    }

    /**
     * Update an existing REST endpoint
     *
     * @param  string $id
     * @param  object|array $data
     * @return ApiProblem|RestEndpoint
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
        $patch = new RestEndpoint();
        $data  = array_merge(array('controller_service_name' => $id), $data);
        $patch->exchangeArray($data);

        try {
            $updated = $model->updateService($patch);
        } catch (\Exception $e) {
            throw new PatchException('Error updating REST endpoint', 500, $e);
        }

        return $updated;
    }
}
