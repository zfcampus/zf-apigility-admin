<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Metadata\Metadata;

/**
 * Class DbAutodiscoveryModel
 *
 * @package ZF\Apigility\Admin\Model
 */
class DbAutodiscoveryModel extends AbstractAutodiscoveryModel
{
    /**
     * @param $module
     * @param $version
     * @param $adapter_name
     * @return array
     */
    public function fetchColumns($module, $version, $adapter_name)
    {
        $tables = [];
        if (!isset($this->config['db']['adapters'])) {
            // error
        }
        $config = $this->config['db']['adapters'];

        $adapter = new Adapter($config[$adapter_name]);

        try {
            $metadata = new Metadata($adapter);
        } catch (\Exception $e) {
            return [];
        }

        $tableNames = $metadata->getTableNames();

        foreach ($tableNames as $tableName) {
            if ($this->moduleHasService($module, $version, $tableName)) {
                continue;
            }

            $tableData = [
                'table_name' => $tableName,
            ];
            $table = $metadata->getTable($tableName);

            $tableData['columns'] = [];

            $constraints = $this->getConstraints($metadata, $tableName);

            /** @var \Zend\Db\Metadata\Object\ColumnObject $column */
            foreach ($table->getColumns() as $column) {
                $item = [
                    'name' => $column->getName(),
                    'type' => $column->getDataType(),
                    'required' => !$column->isNullable(),
                    'filters' => [],
                    'validators' => [],
                    'constraints' => [],
                ];

                foreach ($constraints as $constraint) {
                    if ($column->getName() == $constraint['column']) {
                        $item['constraints'][] = ucfirst(strtolower($constraint['type']));

                        switch (strtoupper($constraint['type'])) {
                            case 'PRIMARY KEY':
                                break;
                            case 'FOREIGN KEY':
                                $constraintObj = $this->getConstraintForColumn(
                                    $metadata,
                                    $tableName,
                                    $column->getName()
                                );

                                $validator = $this->validators['foreign_key'];
                                $referencedColumns = $constraintObj->getReferencedColumns();
                                $validator['options'] = [
                                    'adapter' => $adapter_name,
                                    'table' => $constraintObj->getReferencedTableName(),
                                    //TODO: handle composite key constraint
                                    'field' => $referencedColumns[0]
                                ];
                                $item['validators'][] = $validator;
                                break;
                            case 'UNIQUE':
                                $validator = $this->validators['unique'];
                                $validator['options'] = [
                                    'adapter' => $adapter_name,
                                    'table' => $tableName,
                                    'field' => $column->getName(),
                                ];
                                $item['validators'][] = $validator;
                                break;
                        }
                    }
                }

                if (in_array(strtolower($column->getDataType()), ['varchar', 'text'])) {
                    $item['length'] = $column->getCharacterMaximumLength();
                    if (in_array('Primary key', array_values($item['constraints']))) {
                        unset($item['filters']);
                        unset($item['validators']);
                        $tableData['columns'][] = $item;
                        continue;
                    }
                    $item['filters'] = $this->filters['text'];
                    $validator = $this->validators['text'];
                    $validator['options']['max'] = $column->getCharacterMaximumLength();
                    $item['validators'][] = $validator;
                } elseif (in_array(strtolower($column->getDataType()), [
                    'tinyint', 'smallint', 'mediumint', 'int', 'bigint'])) {
                    $item['length'] = $column->getNumericPrecision();
                    if (in_array('Primary key', array_values($item['constraints']))) {
                        unset($item['filters']);
                        unset($item['validators']);
                        $tableData['columns'][] = $item;
                        continue;
                    }
                    $item['filters'] = $this->filters['integer'];
                }


                $tableData['columns'][] = $item;
            }
            $tables[] = $tableData;
        }
        return $tables;
    }

    /**
     * @param Metadata $metadata
     * @param $tableName
     * @return array
     */
    protected function getConstraints(Metadata $metadata, $tableName)
    {
        $constraints = [];
        /** @var \Zend\Db\Metadata\Object\ConstraintObject $constraint */
        foreach ($metadata->getConstraints($tableName) as $constraint) {
            foreach ($constraint->getColumns() as $column) {
                $constraints[] = [
                    'column' => $column,
                    'type' => $constraint->getType(),
                ];
            }
        }

        return $constraints;
    }

    /**
     * @param Metadata $metadata
     * @param $tableName
     * @param $columnName
     * @return null|\Zend\Db\Metadata\Object\ConstraintObject
     */
    protected function getConstraintForColumn(Metadata $metadata, $tableName, $columnName)
    {
        /** @var \Zend\Db\Metadata\Object\ConstraintObject $constraint */
        foreach ($metadata->getConstraints($tableName) as $constraint) {
            foreach ($constraint->getColumns() as $column) {
                if ($column == $columnName) {
                    return $constraint;
                }
            }
        }
        return null;
    }
}
