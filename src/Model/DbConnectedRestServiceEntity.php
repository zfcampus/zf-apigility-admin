<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

class DbConnectedRestServiceEntity extends RestServiceEntity
{
    protected $adapterName;

    protected $hydratorName = 'Zend\Stdlib\Hydrator\ArraySerializable';

    protected $tableName;

    protected $tableService;

    public function exchangeArray(array $data)
    {
        parent::exchangeArray($data);

        foreach ($data as $key => $value) {
            $key = strtolower($key);
            $key = str_replace('_', '', $key);
            switch ($key) {
                case 'adaptername':
                    $this->adapterName = $value;
                    break;
                case 'hydratorname':
                    $this->hydratorName = $value;
                    break;
                case 'tablename':
                    $this->tableName = $value;
                    if (!isset($this->serviceName)) {
                        $this->serviceName  = $value;
                    }
                    break;
                case 'tableservice':
                    $this->tableService = $value;
                    break;
            }
        }

        if (null === $this->tableName) {
            throw new CreationException('No table name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->adapterName) {
            throw new CreationException('No database adapter name provided; cannot create RESTful resource', 422);
        }

        if (null === $this->entityIdentifierName) {
            $this->entityIdentifierName = 'id';
        }

        if (null === $this->routeIdentifierName) {
            $this->routeIdentifierName = sprintf(
                '%s_id',
                $this->normalizeServiceNameForIdentifier($this->tableName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeServiceNameForRoute($this->tableName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeServiceNameForIdentifier($this->tableName);
        }
    }

    public function getArrayCopy()
    {
        $data = parent::getArrayCopy();
        $data['adapter_name'] = $this->adapterName;
        $data['hydrator_name'] = $this->hydratorName;
        $data['table_name'] = $this->tableName;
        $data['table_service'] = $this->tableService;

        if (!isset($data['service_name']) || empty($data['service_name'])) {
            $data['service_name'] = $this->tableName;
        }

        return $data;
    }
}
