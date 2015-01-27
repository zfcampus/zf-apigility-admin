<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\InputFilter\InputFilterInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;

class ContentNegotiationResource extends AbstractResourceListener
{
    /**
     * @var DbAdapterModel
     */
    protected $model;

    public function __construct(ContentNegotiationModel $model)
    {
        $this->model = $model;
    }

    /**
     * Inject the input filter.
     *
     * Primarily present for testing; input filters will be injected via event
     * normally.
     *
     * @param InputFilterInterface $inputFilter
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        $this->inputFilter = $inputFilter;
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
        $data = $this->getInputFilter()->getValues();

        if (!isset($data['content_name'])) {
            throw new CreationException('Missing content_name', 422);
        }

        $name = $data['content_name'];
        unset($data['content_name']);

        $selectors = array();
        if (isset($data['selectors'])) {
            $selectors = (array) $data['selectors'];
        }

        return $this->model->create($name, $selectors);
    }

    public function patch($id, $data)
    {
        $data = $this->getInputFilter()->getValues();

        if (empty($data) || ! array_key_exists('selectors', $data)) {
            return new ApiProblem(400, 'Invalid data provided for update');
        }

        if (empty($data['selectors'])) {
            return new ApiProblem(400, 'No data provided for update');
        }

        return $this->model->update($id, (array) $data['selectors']);
    }

    public function delete($id)
    {
        $this->model->remove($id);
        return true;
    }
}
