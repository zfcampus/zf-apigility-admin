<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\StaticFilter;
use ZF\Apigility\Admin\Utility;
use ReflectionClass;

class DbConnectedRestServiceModel
{
    /**
     * @var RestServiceModel
     */
    protected $restModel;

    /**
     * @param RestServiceModel $restModel
     */
    public function __construct(RestServiceModel $restModel)
    {
        $this->restModel = $restModel;
    }

    /**
     * Determine if the given entity is DB-connected, and, if so, recast to a DbConnectedRestServiceEntity
     *
     * @param  \Zend\EventManager\Event $e
     * @return null|DbConnectedRestServiceEntity
     */
    public static function onFetch($e)
    {
        $entity = $e->getParam('entity', false);
        if (!$entity) {
            // No entity; nothing to do
            return;
        }

        $config = $e->getParam('config', array());
        if (!isset($config['zf-apigility'])
            || !isset($config['zf-apigility']['db-connected'])
            || !isset($config['zf-apigility']['db-connected'][$entity->resourceClass])
        ) {
            // No DB-connected configuration for this service; nothing to do
            return;
        }
        $config = $config['zf-apigility']['db-connected'][$entity->resourceClass];

        if (!isset($config['table_service'])) {
            $config['table_service'] = sprintf('%s\\Table', $entity->resourceClass);
        }

        $dbConnectedEntity = new DbConnectedRestServiceEntity();
        $dbConnectedEntity->exchangeArray(array_merge($entity->getArrayCopy(), $config));

        // If no override resource class is present, remove it from the returned entity
        if ($e->getParam('fetch', true) && ! isset($config['resource_class'])) {
            $dbConnectedEntity->exchangeArray(array('resource_class' => null));
        }

        return $dbConnectedEntity;
    }

    /**
     * Create a new DB-Connected REST service
     *
     * @param  DbConnectedRestServiceEntity $entity
     * @return DbConnectedRestServiceEntity
     */
    public function createService(DbConnectedRestServiceEntity $entity)
    {
        $restModel         = $this->restModel;
        $resourceName      = StaticFilter::execute($entity->tableName, 'WordUnderscoreToCamelCase');
        $resourceClass     = sprintf(
            '%s\\V%s\\Rest\\%s\\%sResource',
            $this->restModel->module,
            $this->restModel->moduleEntity->getLatestVersion(),
            $resourceName,
            $resourceName
        );
        $controllerService = $restModel->createControllerServiceName($resourceName);
        $entityClass       = $restModel->createEntityClass($resourceName, 'entity-db-connected');
        $collectionClass   = $restModel->createCollectionClass($resourceName);
        $routeName         = $restModel->createRoute(
            $resourceName,
            $entity->routeMatch,
            $entity->routeIdentifierName,
            $controllerService
        );
        $mediaType         = $restModel->createMediaType();

        $entity->exchangeArray(array(
            'collection_class'        => $collectionClass,
            'controller_service_name' => $controllerService,
            'entity_class'            => $entityClass,
            'module'                  => $restModel->module,
            'resource_class'          => $resourceClass,
            'route_name'              => $routeName,
            'accept_whitelist'        => array(
                $mediaType,
                'application/hal+json',
                'application/json',
            ),
            'content_type_whitelist'  => array(
                $mediaType,
                'application/json',
            ),
        ));

        $restModel->createRestConfig($entity, $controllerService, $resourceClass, $routeName);
        $restModel->createContentNegotiationConfig($entity, $controllerService);
        $restModel->createHalConfig($entity, $entityClass, $collectionClass, $routeName);

        $this->createDbConnectedConfig($entity);

        return $entity;
    }

    /**
     * Update a DB-Connected service
     *
     * @param  DbConnectedRestServiceEntity $entity
     * @return DbConnectedRestServiceEntity
     */
    public function updateService(DbConnectedRestServiceEntity $entity)
    {
        $updatedEntity  = $this->restModel->updateService($entity);

        // We need the resource class in order to update db-connected config!
        if (! $entity->resourceClass && $updatedEntity->resourceClass) {
            $entity->exchangeArray(array(
                'resource_class' => $updatedEntity->resourceClass,
            ));
        }

        $updatedProps   = $this->updateDbConnectedConfig($entity);
        $updatedEntity->exchangeArray($updatedProps);
        $this->updateHalConfig($entity);

        // Determine whether or not the resource class should be returned with the entity
        $config = $this->restModel->configResource->fetch(true);
        $config = $config['zf-apigility']['db-connected'][$entity->resourceClass];
        if (! isset($config['resource_class'])) {
            $entity->exchangeArray(array('resource_class' => null));
        }

        return $updatedEntity;
    }

    /**
     * Delete a DB-Connected service
     *
     * @param  DbConnectedRestServiceEntity $entity
     * @param  bool $recursive
     * @return true
     */
    public function deleteService(DbConnectedRestServiceEntity $entity, $recursive = false)
    {
        $this->restModel->deleteService($entity->controllerServiceName);
        $this->deleteDbConnectedConfig($entity);

        if ($recursive) {
            $reflection = new ReflectionClass($entity->entityClass);
            Utility::recursiveDelete(dirname($reflection->getFileName()));
        }
        return true;
    }

    /**
     * Create DB-Connected configuration based on entity
     *
     * @param  DbConnectedRestServiceEntity $entity
     */
    public function createDbConnectedConfig(DbConnectedRestServiceEntity $entity)
    {
        $entity->exchangeArray(array(
            'table_service' => sprintf('%s\\Table', $entity->resourceClass),
        ));

        $config = array('zf-apigility' => array('db-connected' => array(
            $entity->resourceClass => array(
                'adapter_name'            => $entity->adapterName,
                'table_name'              => $entity->tableName,
                'hydrator_name'           => $entity->hydratorName,
                'controller_service_name' => $entity->controllerServiceName,
                'entity_identifier_name'  => $entity->entityIdentifierName,
            ),
        )));
        $this->restModel->configResource->patch($config, true);
    }

    /**
     * Update the DB-Connected configuration for the entity
     *
     * @param  DbConnectedRestServiceEntity $entity
     */
    public function updateDbConnectedConfig(DbConnectedRestServiceEntity $entity)
    {
        $properties = array('zf-apigility' => array('db-connected' => array(
            $entity->resourceClass => array(
                'adapter_name'           => $entity->adapterName,
                'table_name'             => $entity->tableName,
                'table_service'          => $entity->tableService,
                'hydrator_name'          => $entity->hydratorName,
                'entity_identifier_name' => $entity->entityIdentifierName,
            ),
        )));
        $this->restModel->configResource->patch($properties, true);
        return $properties['zf-apigility']['db-connected'][$entity->resourceClass];
    }

    /**
     * Update the HAL configuration for the service
     *
     * @param  RestServiceEntity $original
     * @param  RestServiceEntity $update
     */
    public function updateHalConfig(DbConnectedRestServiceEntity $entity)
    {
        $baseKey     = 'zf-hal.metadata_map';
        $entityClass = $entity->entityClass;
        if (isset($entity->hydratorName) && $entity->hydratorName) {
            $key = sprintf('%s.%s.hydrator', $baseKey, $entityClass);
            $this->restModel->configResource->patchKey($key, $entity->hydratorName);
        }
    }

    /**
     * Delete the DB-Connected configuration for the entity
     *
     * @param  DbConnectedRestServiceEntity $entity
     */
    public function deleteDbConnectedConfig(DbConnectedRestServiceEntity $entity)
    {
        $key = array('zf-apigility', 'db-connected', $entity->resourceClass);
        $this->restModel->configResource->deleteKey($key);
    }
}
