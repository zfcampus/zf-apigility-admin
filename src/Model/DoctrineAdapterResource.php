<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\Exception\CreationException;
use ZF\Rest\AbstractResourceListener;

class DoctrineAdapterResource extends AbstractResourceListener
{
    /**
     * @var DbAdapterModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param DoctrineAdapterModel $model
     */
    public function __construct(DoctrineAdapterModel $model)
    {
        $this->model = $model;
    }

    /**
     * @param $id
     * @return DbAdapterEntity|ApiProblem
     */
    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (!$entity) {
            return new ApiProblem(404, 'Adapter not found');
        }
        return $entity;
    }

    /**
     * @param array $params
     * @return array
     */
    public function fetchAll($params = array())
    {
        return $this->model->fetchAll($params);
    }

    /**
     * @param $data
     * @return DbAdapterEntity
     */
    public function create($data)
    {
        if (is_object($data)) {
            $data = (array)$data;
            if (!isset($data['doctrine_adapter_name'])) {
                throw new CreationException('Missing doctrine_adapter_name', 422);
            }

            $name = $data['odctrine_adapter_name'];
            unset($data['odctrine_adapter_name']);

            return $this->model->create($name, $data);
        }
        return false;
    }

    /**
     * @param $id
     * @param $data
     * @return DbAdapterEntity|ApiProblem
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

        return $this->model->update($id, $data);
    }

    /**
     * @param $id
     * @return bool
     */
    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}