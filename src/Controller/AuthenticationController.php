<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\Apigility\Admin\Model\AuthenticationModel;
use ZF\Apigility\Admin\Model\AuthenticationEntity;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Entity;
use ZF\Hal\Collection;
use ZF\Hal\Link\Link;
use Zend\Http\Request;
use ZF\Apigility\Admin\Exception;

class AuthenticationController extends AbstractAuthenticationController
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
                if (! $adapter) {
                    return $this->fetchAuthenticationCollection();
                }

                return $this->fetchAuthenticationEntity($adapter);
            case $request::METHOD_POST:
                if ($adapter) {
                    $response = new ApiProblemResponse(
                        new ApiProblem(405, 'Only the methods GET, PUT, and DELETE are allowed for this URI')
                    );
                    $response->getHeaders()->addHeaderLine('Allow', 'GET, PUT, DELETE');
                    return $response;
                }

                return $this->createAuthenticationAdapter($this->bodyParams());
            case $request::METHOD_PUT:
                return $this->updateAuthenticationAdapter($adapter, $this->bodyParams());
            case $request::METHOD_DELETE:
                return $this->removeAuthenticationAdapter($adapter);
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, POST, PUT, and DELETE are allowed for this URI')
                );
        }
    }

    /**
     * Mapping action for v2
     * Since Apigility 1.1
     */
    public function mappingAction()
    {
        $request = $this->getRequest();
        $version = $this->getVersion($request);

        switch ($version) {
            case 1:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'This API is supported starting from version 2')
                );
            case 2:
                return $this->mappingAuthentication($request);
            default:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'The API version specified is not supported')
                );
        }
    }

    /**
     * Map the authentication adapter to a module
     * Since Apigility 1.1
     *
     * @param  Request $request
     * @return ViewModel
     */
    protected function mappingAuthentication(Request $request)
    {
        $module  = $this->params('name', false);
        $version = $this->params()->fromQuery('version', false);

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                return $this->createAuthenticationMapResult(
                    $this->model->getAuthenticationMap($module, $version)
                );
            case $request::METHOD_PUT:
                return $this->updateAuthenticationMap($this->bodyParams(), $module, $version);
            case $request::METHOD_DELETE:
                return $this->removeAuthenticationMap($module, $version);
            default:
                $response = new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET, PUT, DELETE are allowed for this URI')
                );
                $response->getHeaders()->addHeaderLine('Allow', 'GET, PUT, DELETE');
                return $response;
        }
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
     * Fetch a collection of authentication adapters
     *
     * @return ViewModel
     */
    private function fetchAuthenticationCollection()
    {
        $collection = $this->model->fetchAllAuthenticationAdapter();
        if (! $collection) {
            // Check for old authentication configuration
            if ($this->model->fetch()) {
                // Create a new authentication adapter for each API/version
                $this->model->transformAuthPerApis();
                $collection = $this->model->fetchAllAuthenticationAdapter();
            }
        }

        return $this->createCollection($collection);
    }

    /**
     * Fetch an authentication entity
     *
     * @param string $adapter
     * @return ApiProblemResponse|ViewModel
     */
    private function fetchAuthenticationEntity($adapter)
    {
        $entity = $this->model->fetchAuthenticationAdapter($adapter);
        if (! $entity) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'No authentication adapter found')
            );
        }

        return $this->createEntity($entity);
    }

    /**
     * Create a new authentication adapter
     *
     * @param array $params
     * @return ApiProblemResponse|\Zend\Http\Response
     */
    private function createAuthenticationAdapter($params)
    {
        try {
            $entity = $this->model->createAuthenticationAdapter($params);
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

        return $this->createEntity($entity);
    }

    /**
     * Update an existing authentication adapter
     *
     * @return ApiProblemResponse|ViewModel
     */
    private function updateAuthenticationAdapter($adapter, $params)
    {
        $entity = $this->model->updateAuthenticationAdapter($adapter, $params);
        if (! $entity) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'No authentication adapter found')
            );
        }

        return $this->createEntity($entity);
    }

    /**
     * Remove an existing authentication adapter
     *
     * @return ApiProblemResponse|\Zend\Http\Response
     */
    private function removeAuthenticationAdapter($adapter)
    {
        if (! $this->model->removeAuthenticationAdapter($adapter)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'No authentication adapter found')
            );
        }

        return $this->getResponse()->setStatusCode(204);
    }

    /**
     * Attempt to save an authentication map.
     *
     * The authentication map maps between the given authentication adapter and
     * the selected module/version pair.
     *
     * @param array $params
     * @param string $module
     * @param string|int $version
     * @return ViewModel|ApiProblemResponse
     */
    private function updateAuthenticationMap($params, $module, $version)
    {
        if (! isset($params['authentication'])) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'No authentication adapter found')
            );
        }

        try {
            $this->model->saveAuthenticationMap($params['authentication'], $module, $version);
        } catch (Exception\InvalidArgumentException $e) {
            return new ApiProblemResponse(
                new ApiProblem($e->getCode(), $e->getMessage())
            );
        }

        return $this->createAuthenticationMapResult($params['authentication']);
    }

    /**
     * Remove the authentication map for a given module/version pair.
     *
     * @param string $module
     * @param string|int $version
     * @return ApiProblemResponse|\Zend\Http\Response
     */
    private function removeAuthenticationMap($module, $version)
    {
        try {
            $this->model->removeAuthenticationMap($module, $version);
        } catch (Exception\InvalidArgumentException $e) {
            return new ApiProblemResponse(
                new ApiProblem($e->getCode(), $e->getMessage())
            );
        }
        $response = $this->getResponse();
        $response->setStatusCode(204);
        return $response;
    }

    /**
     * Create a collection response
     *
     * @param mixed $collection
     * @return ViewModel
     */
    private function createCollection($collection)
    {
        $halCollection = array();
        foreach ($collection as $entity) {
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
    }

    /**
     * Create and return an entity view model
     *
     * @param mixed $entity
     * @return ViewModel
     */
    private function createEntity($entity)
    {
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

    /**
     * Create a view model detailing the authentication adapter mapped
     *
     * @param string $adapter
     * @return ViewModel
     */
    private function createAuthenticationMapResult($adapter)
    {
        $model = new ViewModel(array(
            'authentication' => $adapter
        ));
        $model->setTerminal(true);
        return $model;
    }
}
