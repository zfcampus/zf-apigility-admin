<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class AuthenticationController extends AbstractActionController
{
    protected $model;

    public function __construct(AuthenticationModel $model)
    {
        $this->model = $model;
    }

    public function authenticationAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                return $this->model->fetch();
            case $request::METHOD_POST:
                $data = $this->bodyParams();
                return $this->model->create($data);
            case $request::METHOD_PATCH:
                $data = $this->bodyParams();
                return $this->model->update($data);
            case $request::METHOD_DELETE:
                return $this->model->remove();
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, POST, PATCH, and DELETE are allowed for this URI')
                );
        }
    }

    /**
     * Set the request object manually
     *
     * Provided for testing.
     *
     * @param  Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
