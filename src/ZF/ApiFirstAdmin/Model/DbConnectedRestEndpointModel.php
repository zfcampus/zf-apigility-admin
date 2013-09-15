<?php

namespace ZF\ApiFirstAdmin\Model;

class DbConnectedRestEndpointModel
{
    /**
     * @var RestEndpointModel
     */
    protected $restModel;

    /**
     * @param RestEndpointModel $restModel
     */
    public function __construct(RestEndpointModel $restModel)
    {
        $this->restModel = $restModel;
    }

    /**
     * Determine if the given entity is DB-connected, and, if so, recast to a DbConnectedRestEndpointEntity
     *
     * @param  \Zend\EventManager\Event $e
     * @return null|DbConnectedRestEndpointEntity
     */
    public static function onFetch($e)
    {
        $entity = $e->getParam('entity', false);
        if (!$entity) {
            // No entity; nothing to do
            return;
        }

        $config = $e->getParam('config', array());
        if (!isset($config['zf-api-first'])
            || !isset($config['zf-api-first']['db-connected'])
            || !isset($config['zf-api-first']['db-connected'][$entity->resourceClass])
        ) {
            // No DB-connected configuration for this service; nothing to do
            return;
        }
        $config = $config['zf-api-first']['db-connected'][$entity->resourceClass];

        if (!isset($config['table_service'])) {
            $config['table_service'] = sprintf('%s\\Table', $entity->resourceClass);
        }

        $dbConnectedEntity = new DbConnectedRestEndpointEntity();
        $dbConnectedEntity->exchangeArray(array_merge($entity->getArrayCopy(), $config));
        return $dbConnectedEntity;
    }

    /**
     * Create a new DB-Connected REST endpoint
     *
     * @param  DbConnectedRestEndpointEntity $entity
     * @return DbConnectedRestEndpointEntity
     */
    public function createService(DbConnectedRestEndpointEntity $entity)
    {
        $restModel         = $this->restModel;
        $resourceName      = ucfirst($entity->tableName);
        $resourceClass     = sprintf('%s\\Rest\\%s\\%sResource', $this->restModel->module, $resourceName, $resourceName);
        $controllerService = $restModel->createControllerServiceName($resourceName);
        $entityClass       = $restModel->createEntityClass($resourceName);
        $collectionClass   = $restModel->createCollectionClass($resourceName);
        $routeName         = $restModel->createRoute($resourceName, $entity->routeMatch, $entity->identifierName, $controllerService);
        $restModel->createRestConfig($entity, $controllerService, $resourceClass, $routeName);
        $restModel->createContentNegotiationConfig($entity, $controllerService);
        $restModel->createHalConfig($entity, $entityClass, $collectionClass, $routeName);

        $entity->exchangeArray(array(
            'collection_class'        => $collectionClass,
            'controller_service_name' => $controllerService,
            'entity_class'            => $entityClass,
            'module'                  => $restModel->module,
            'resource_class'          => $resourceClass,
            'route_name'              => $routeName,
        ));

        $this->createDbConnectedConfig($entity);

        return $entity;
    }

    /**
     * Update a DB-Connected endpoint
     *
     * @param  DbConnectedRestEndpointEntity $entity
     * @return DbConnectedRestEndpointEntity
     */
    public function updateService(DbConnectedRestEndpointEntity $entity)
    {
        $updatedEntity = $this->restModel->updateService($entity);
        $updatedProps  = $this->updateDbConnectedConfig($entity);
        $updatedEntity->exchangeArray($updatedProps);
        return $updatedEntity;
    }

    /**
     * Deelte a DB-Connected endpoint
     *
     * @param  DbConnectedRestEndpointEntity $entity
     * @return true
     */
    public function deleteService(DbConnectedRestEndpointEntity $entity)
    {
        $this->restModel->deleteService($entity->controllerServiceName);
        $this->deleteDbConnectedConfig($entity);
        return true;
    }

    /**
     * Create DB-Connected configuration based on entity
     *
     * @param  DbConnectedRestEndpointEntity $entity
     */
    public function createDbConnectedConfig(DbConnectedRestEndpointEntity $entity)
    {
        $entity->exchangeArray(array(
            'table_service' => sprintf('%s\\Table', $entity->resourceClass),
        ));

        $config = array('zf-api-first' => array('db-connected' => array(
            $entity->resourceClass => array(
                'adapter_name'  => $entity->adapterName,
                'table_name'    => $entity->tableName,
                'hydrator_name' => $entity->hydratorName,
            ),
        )));
        $this->restModel->configResource->patch($config, true);
    }

    /**
     * Update the DB-Connected configuration for the entity
     *
     * @param  DbConnectedRestEndpointEntity $entity
     */
    public function updateDbConnectedConfig(DbConnectedRestEndpointEntity $entity)
    {
        $properties = array('zf-api-first' => array('db-connected' => array(
            $entity->resourceClass => array(
                'adapter_name'  => $entity->adapterName,
                'table_name'    => $entity->tableName,
                'table_service' => $entity->tableService,
                'hydrator_name' => $entity->hydratorName,
            ),
        )));
        $this->restModel->configResource->patch($properties, true);
        return $properties['zf-api-first']['db-connected'][$entity->resourceClass];
    }

    /**
     * Delete the DB-Connected configuration for the entity
     *
     * @param  DBConnectedRestEndpointEntity $entity
     */
    public function deleteDbConnectedConfig(DBConnectedRestEndpointEntity $entity)
    {
        $key = array('zf-api-first', 'db-connected', $entity->resourceClass);
        $this->restModel->configResource->deleteKey($key);
    }
}
