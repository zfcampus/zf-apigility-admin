<?php

namespace ZF\ApiFirstAdmin\Model;

class DbConnectedRestEndpointEntity extends RestEndpointEntity
{
    protected $adapterName;

    protected $hydratorName = 'ArraySerializable';

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

        if (null === $this->identifierName) {
            $this->identifierName = sprintf(
                '%s_id',
                $this->normalizeResourceNameForIdentifier($this->tableName)
            );
        }

        if (null === $this->routeMatch) {
            $this->routeMatch = sprintf(
                '/%s',
                $this->normalizeResourceNameForRoute($this->tableName)
            );
        }

        if (null === $this->collectionName) {
            $this->collectionName = $this->normalizeResourceNameForIdentifier($this->tableName);
        }
    }

    public function getArrayCopy()
    {
        $data = parent::getArrayCopy();
        $data['adapter_name'] = $this->adapterName;
        $data['hydrator_name'] = $this->hydratorName;
        $data['table_name'] = $this->tableName;
        $data['table_service'] = $this->tableService;
        return $data;
    }
}
