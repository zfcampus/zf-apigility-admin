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
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Resource;
use ZF\Hal\Link\Link;

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
                $entity = $this->model->fetch();
                if (!$entity) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'No authentication configuration found')
                    );
                }
                break;
            case $request::METHOD_POST:
                $entity = $this->model->create($this->bodyParams());
                break;
            case $request::METHOD_PATCH:
                $entity = $this->model->update($this->bodyParams());
                break;
            case $request::METHOD_DELETE:
                if ($this->model->remove()) {
                    return $this->getResponse()->setStatusCode(204);
                }
                return new ApiProblemResponse(
                    new ApiProblem(404, 'No authentication configuration found')
                );
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, POST, PATCH, and DELETE are allowed for this URI')
                );
        }

        $resource = new Resource($entity, null);
        $resource->getLinks()->add(Link::factory(array(
            'rel' => 'self',
            'route' => 'zf-apigility-admin/api/authentication',
        )));
        $model = new ViewModel(array('payload' => $resource));
        $model->setTerminal(true);
        return $model;
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
