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
     * @var RestEndpointModel
     */
    protected $model;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var RestEndpointModelFactory
     */
    protected $restFactory;

    /**
     * @param  RestEndpointModelFactory $restFactory
     */
    public function __construct(RestEndpointModelFactory $restFactory)
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
     * @return RestEndpointModel
     */
    public function getModel($type = RestEndpointModelFactory::TYPE_DEFAULT)
    {
        if ($this->model instanceof RestEndpointModel) {
            return $this->model;
        }
        $moduleName = $this->getModuleName();
        $this->model = $this->restFactory->factory($moduleName, $type);
        return $this->model;
    }

    /**
     * Create a new REST endpoint
     *
     * @param  array|object $data
     * @return RestEndpointEntity
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $type = RestEndpointModelFactory::TYPE_DEFAULT;
        if (isset($data['table_name'])) {
            $creationData = new DbConnectedRestEndpointEntity();
            $type = RestEndpointModelFactory::TYPE_DB_CONNECTED;
        } else {
            $creationData = new NewRestEndpointEntity();
        }

        $creationData->exchangeArray($data);
        $model = $this->getModel($type);

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
     * @return RestEndpointEntity|ApiProblem
     */
    public function fetch($id)
    {
        $endpoint = $this->getModel()->fetch($id);
        if (!$endpoint instanceof RestEndpointEntity) {
            return new ApiProblem(404, 'REST endpoint not found');
        }
        return $endpoint;
    }

    /**
     * Fetch metadata for all REST endpoints
     *
     * @param  array $params
     * @return RestEndpointEntity[]
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
     * @return ApiProblem|RestEndpointEntity
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

        // Make sure we have an entity first
        $model  = $this->getModel();
        $entity = $model->fetch($id);

        $entity->exchangeArray($data);

        try {
            switch (true) {
                case ($entity instanceof DbConnectedRestEndpointEntity):
                    $model   = $this->restFactory->factory($this->getModuleName(), RestEndpointModelFactory::TYPE_DB_CONNECTED);
                    $updated = $model->updateService($entity);
                    break;
                case ($entity instanceof RestEndpointEntity):
                default:
                    $updated = $model->updateService($entity);
            }
        } catch (\Exception $e) {
            throw new PatchException('Error updating REST endpoint', 500, $e);
        }

        return $updated;
    }

    /**
     * Delete an endpoint
     *
     * @param  string $id
     * @return true
     */
    public function delete($id)
    {
        // Make sure we have an entity first
        $model  = $this->getModel();
        $entity = $model->fetch($id);

        try {
            switch (true) {
                case ($entity instanceof DbConnectedRestEndpointEntity):
                    $model   = $this->restFactory->factory($this->getModuleName(), RestEndpointModelFactory::TYPE_DB_CONNECTED);
                    $model->deleteService($entity);
                    break;
                case ($entity instanceof RestEndpointEntity):
                default:
                    $model->deleteService($entity->controllerServiceName);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error deleting REST endpoint', 500, $e);
        }

        return true;
    }
}
