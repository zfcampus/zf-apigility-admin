<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use ZF\Apigility\Admin\Model\ValidatorsModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class ValidatorsController extends AbstractActionController
{
    protected $model;

    public function __construct(ValidatorsModel $model)
    {
        $this->model = $model;
    }

    public function validatorsAction()
    {
        $request = $this->getRequest();

        if ($request->getMethod() != $request::METHOD_GET) {
            return new ApiProblemResponse(
                new ApiProblem(405, 'Only the GET method is allowed for this URI')
            );
        }

        $model = new JsonModel(['validators' => $this->model->fetchAll()]);
        $model->setTerminal(true);
        return $model;
    }
}
