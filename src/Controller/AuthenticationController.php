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
use ZF\Hal\Collection;
use ZF\Hal\Link\Link;
use Zend\Http\Request;

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
        $version = $this->getVersion($request);

        switch ($version) {
            case 1:
                return $this->authVersion1($request);
            case 2:
                return $this->authVersion2($request);
            default:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'The API version specified is not supported')
                );
        }
    }

    /**
     * Manage the authentication API version 1
     *
     * @param  Request $request
     * @return ViewModel
     */
    protected function authVersion1(Request $request)
    {
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
     * Manage the authentication API version 2
     *
     * @param  Request $request
     * @return ViewModel
     */
    protected function authVersion2(Request $request)
    {
        $adapter = $this->params('authentication_adapter', false);
        if ($adapter) {
          $adapter = strtolower($adapter);
        }
        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                if (!$adapter) {
                    $collection = $this->model->fetchAll();
                } else {
                    $entity = $this->model->fetchByName($adapter);
                    if (!$entity) {
                        return new ApiProblemResponse(
                            new ApiProblem(404, 'No authentication adapter found')
                        );
                    }
                }
                break;
            case $request::METHOD_POST:
                if ($adapter) {
                    return new ApiProblemResponse(
                        new ApiProblem(405, 'Only the methods GET, PUT, and DELETE are allowed for this URI')
                    );
                }
                try {
                    $entity = $this->model->createVersion2($this->bodyParams());
                } catch (\Exception $e) {
                    return new ApiProblemResponse(
                        new ApiProblem($e->getCode(), $e->getMessage())
                    );
                }
                $response = $this->getResponse();
                $response->setStatusCode(201);
                $response->getHeaders()->addHeaderLine(
                    'Location',
                    $this->url()->fromRoute(
                        'zf-apigility/api/authentication',
                        array( 'authentication_adapter' => $entity['name'] )
                    )
                );
                break;
            case $request::METHOD_PUT:
                $entity = $this->model->updateVersion2($adapter, $this->bodyParams());
                if (!$entity) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'No authentication adapter found')
                    );
                }
                break;
            case $request::METHOD_DELETE:
                if ($this->model->removeVersion2($adapter)) {
                    return $this->getResponse()->setStatusCode(204);
                }
                return new ApiProblemResponse(
                    new ApiProblem(404, 'No authentication adapter found')
                );
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, POST, PUT, and DELETE are allowed for this URI')
                );
        }

        if (isset($collection)) {
            $halCollection = array();
            foreach($collection as $entity) {
                $halEntity = new Entity($entity, 'name');
                $halEntity->getLinks()->add(Link::factory(array(
                    'rel' => 'self',
                    'route' => array(
                        'name'   => 'zf-apigility/api/authentication',
                        'params' => array('authentication_adapter' => $entity['name'])
                    )
                )));
                $halCollection[] = $halEntity;
            }
            return new ViewModel(array('payload' => new Collection($halCollection)));
        } else {
            $halEntity = new Entity($entity, 'name');
            $halEntity->getLinks()->add(Link::factory(array(
                'rel' => 'self',
                'route' => array(
                    'name'   => 'zf-apigility/api/authentication',
                    'params' => array('authentication_adapter' => $entity['name'])
                )
            )));
            return new ViewModel(array('payload' => $halEntity));
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

    /**
     * Get the API version from the Accept header
     *
     * @param  Request $request
     * @return integer
     */
    protected function getVersion(Request $request)
    {
        $accept = $request->getHeader('Accept', false);
        if ($accept) {
          if (preg_match('/application\/vnd\.apigility\.v(\d+)\+json/', $accept->getFieldValue(), $matches)) {
            return (int) $matches[1];
          }
        }
        return 1;
    }
}
