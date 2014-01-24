<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use RuntimeException;
use ZF\ApiProblem\ApiProblem;
use ZF\Hal\Collection as HalCollection;
use ZF\Hal\Link\Link;
use ZF\Hal\Resource as HalResource;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;
use ZF\Rest\Exception\PatchException;

class RestServiceResource extends AbstractResourceListener
{
    /**
     * @var InputFilterModel
     */
    protected $inputFilterModel;

    /**
     * @var DocumentationModel
     */
    protected $documentationModel;

    /**
     * @var RestServiceModel
     */
    protected $model;

    /**
     * @var string
     */
    protected $moduleName;

    /**
     * @var RestServiceModelFactory
     */
    protected $restFactory;

    /**
     * @param  RestServiceModelFactory $restFactory
     * @param  InputFilterModel $inputFilterModel
     */
    public function __construct(RestServiceModelFactory $restFactory, InputFilterModel $inputFilterModel, DocumentationModel $documentationModel)
    {
        $this->restFactory = $restFactory;
        $this->inputFilterModel = $inputFilterModel;
        $this->documentationModel = $documentationModel;
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
     * @return RestServiceModel
     */
    public function getModel($type = RestServiceModelFactory::TYPE_DEFAULT)
    {
        if ($this->model instanceof RestServiceModel) {
            return $this->model;
        }
        $moduleName = $this->getModuleName();
        $this->model = $this->restFactory->factory($moduleName, $type);
        return $this->model;
    }

    /**
     * Create a new REST service
     *
     * @param  array|object $data
     * @return RestServiceEntity
     * @throws CreationException
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $type = RestServiceModelFactory::TYPE_DEFAULT;
        if (isset($data['table_name'])) {
            $creationData = new DbConnectedRestServiceEntity();
            $type = RestServiceModelFactory::TYPE_DB_CONNECTED;
        } else {
            $creationData = new NewRestServiceEntity();
        }

        $creationData->exchangeArray($data);
        $model = $this->getModel($type);

        try {
            $service = $model->createService($creationData);
        } catch (\Exception $e) {
            throw new CreationException('Unable to create REST service', $e->getCode(), $e);
        }

        return $service;
    }

    /**
     * Fetch REST metadata
     *
     * @param  string $id
     * @return RestServiceEntity|ApiProblem
     */
    public function fetch($id)
    {
        $service = $this->getModel()->fetch($id);
        if (!$service instanceof RestServiceEntity) {
            return new ApiProblem(404, 'REST service not found');
        }

        $this->injectInputFilters($service);
        $this->injectDocumentation($service);
        return $service;
    }

    /**
     * Fetch metadata for all REST services
     *
     * @param  array $params
     * @return RestServiceEntity[]
     */
    public function fetchAll($params = array())
    {
        $version  = $this->getEvent()->getQueryParam('version', null);
        $services = $this->getModel()->fetchAll($version);

        foreach ($services as $service) {
            $this->injectInputFilters($service);
            $this->injectDocumentation($service);
        }

        return $services;
    }

    /**
     * Update an existing REST service
     *
     * @param  string $id
     * @param  object|array $data
     * @return ApiProblem|RestServiceEntity
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
                case ($entity instanceof DbConnectedRestServiceEntity):
                    $model   = $this->restFactory->factory($this->getModuleName(), RestServiceModelFactory::TYPE_DB_CONNECTED);
                    $updated = $model->updateService($entity);
                    break;
                case ($entity instanceof RestServiceEntity):
                default:
                    $updated = $model->updateService($entity);
            }
        } catch (\Exception $e) {
            throw new PatchException('Error updating REST service', 500, $e);
        }

        return $updated;
    }

    /**
     * Delete a service
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
                case ($entity instanceof DbConnectedRestServiceEntity):
                    $model   = $this->restFactory->factory($this->getModuleName(), RestServiceModelFactory::TYPE_DB_CONNECTED);
                    $model->deleteService($entity);
                    break;
                case ($entity instanceof RestServiceEntity):
                default:
                    $model->deleteService($entity->controllerServiceName);
            }
        } catch (\Exception $e) {
            throw new \Exception('Error deleting REST service', 500, $e);
        }

        return true;
    }

    /**
     * Inject the input filters collection, if any, as an embedded collection
     *
     * @param RestServiceEntity $service
     */
    protected function injectInputFilters(RestServiceEntity $service)
    {
        $inputFilters = $this->inputFilterModel->fetch($this->moduleName, $service->controllerServiceName);
        if (!$inputFilters instanceof InputFilterCollection
            || !count($inputFilters)
        ) {
            return;
        }

        $collection = [];

        foreach ($inputFilters as $inputFilter) {
            $resource = new HalResource($inputFilter, $inputFilter['input_filter_name']);
            $links    = $resource->getLinks();
            $links->add(Link::factory([
                'rel' => 'self',
                'route' => [
                    'name' => 'zf-apigility-admin/api/module/rest-service/input-filter',
                    'params' => [
                        'name' => $this->moduleName,
                        'controller_service_name' => $service->controllerServiceName,
                        'input_filter_name' => $inputFilter['input_filter_name'],
                    ],
                ],
            ]));
            $collection[] = $resource;
        }

        $collection = new HalCollection($collection);
        $collection->setCollectionName('input_filter');
        $collection->setCollectionRoute('zf-apigility-admin/module/rest-service/input-filter');
        $collection->setCollectionRouteParams([
            'name' => $service->module,
            'controller_service_name' => $service->controllerServiceName,
        ]);

        $service->exchangeArray([
            'input_filters' => $collection,
        ]);
    }

    protected function injectDocumentation(RestServiceEntity $service)
    {
        $documentation = $this->documentationModel->fetchDocumentation($this->moduleName, $service->controllerServiceName);
        if (!$documentation) {
            return;
        }
        $resource = new HalResource($documentation, 'documentation');

        $service->exchangeArray(['documentation' => $resource]);
    }
}
