<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Model\AuthenticationEntity;
use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Entity;
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
                    $response = $this->getResponse();
                    $response->setStatusCode(204);
                    return $response;
                }
                break;
            case $request::METHOD_POST:
                $entity = $this->model->create($this->bodyParams());
                $response = $this->getResponse();
                $response->setStatusCode(201);
                $response->getHeaders()->addHeaderLine(
                    'Location',
                    $this->plugin('hal')->createLink($this->getRouteForEntity($entity))
                );
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

        $halEntity = new Entity($entity, null);
        $halEntity->getLinks()->add(Link::factory(array(
            'rel' => 'self',
            'route' => $this->getRouteForEntity($entity),
        )));
        return new ViewModel(array('payload' => $halEntity));
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

    /**
     * Determine the route to use for a given entity
     *
     * @param  AuthenticationEntity $entity
     * @return string
     */
    protected function getRouteForEntity(AuthenticationEntity $entity)
    {
        $baseRoute = 'zf-apigility/api/authentication';

        if ($entity->isBasic()) {
            return $baseRoute . '/http-basic';
        }

        if ($entity->isDigest()) {
            return $baseRoute . '/http-digest';
        }

        if ($entity->isOAuth2()) {
            return $baseRoute . '/oauth2';
        }

        return $baseRoute;
    }
}
