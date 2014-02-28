<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;

class DbAdapterResource extends AbstractResourceListener
{
    /**
     * @var DbAdapterModel
     */
    protected $model;

    public function __construct(DbAdapterModel $model)
    {
        $this->model = $model;
    }

    public function fetch($id)
    {
        $entity = $this->model->fetch($id);
        if (!$entity) {
            return new ApiProblem(404, 'Adapter not found');
        }
        return $entity;
    }

    public function fetchAll($params = array())
    {
        return $this->model->fetchAll();
    }

    public function create($data)
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        if (!isset($data['adapter_name'])) {
            throw new CreationException('Missing adapter_name', 422);
        }

        $name = $data['adapter_name'];
        unset($data['adapter_name']);

        return $this->model->create($name, $data);
    }

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

    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}
