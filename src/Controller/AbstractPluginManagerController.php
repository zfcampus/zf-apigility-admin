<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

abstract class AbstractPluginManagerController extends AbstractActionController
{
    /**
     * @var object
     */
    protected $model;

    /**
     * @var string Name of property in view model to which values are assigned
     */
    protected $property;

    /**
     * Call this method from the appropriate action method
     *
     * @return ApiProblemResponse|JsonModel
     */
    public function handleRequest()
    {
        $request = $this->getRequest();

        if ($request->getMethod() != $request::METHOD_GET) {
            return new ApiProblemResponse(
                new ApiProblem(405, 'Only the GET method is allowed for this URI')
            );
        }

        $model = new JsonModel(array($this->property => $this->model->fetchAll()));
        $model->setTerminal(true);
        return $model;
    }
}
