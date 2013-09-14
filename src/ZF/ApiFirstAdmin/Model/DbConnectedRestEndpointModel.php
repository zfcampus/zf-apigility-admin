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
}
