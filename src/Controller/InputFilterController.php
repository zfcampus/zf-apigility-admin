<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Model\InputFilterCollection;
use ZF\Apigility\Admin\Model\InputFilterModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Collection as HalCollection;
use ZF\Hal\Link\Link;
use ZF\Hal\Entity as HalEntity;

class InputFilterController extends AbstractActionController
{
    protected $model;

    public function __construct(InputFilterModel $model)
    {
        $this->model = $model;
    }

    public function indexAction()
    {
        $event           = $this->getEvent();
        $routeMatch      = $event->getRouteMatch();
        $route           = $this->deriveRouteName($routeMatch->getMatchedRouteName());
        $request         = $this->getRequest();
        $module          = $this->params()->fromRoute('name', false);
        $controller      = $this->params()->fromRoute('controller_service_name', false);
        $inputFilterName = $this->params()->fromRoute('input_filter_name', false);

        if (!$module || !$this->model->moduleExists($module)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The module specified does not exist')
            );
        }

        if (!$controller || !$this->model->controllerExists($module, $controller)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The controller specified does not exist')
            );
        }

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                $result = $this->model->fetch($module, $controller, $inputFilterName);
                if (false === $result) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input filter specified does not exist')
                    );
                }

                if ($result instanceof InputFilterCollection) {
                    $result = new HalCollection($result);
                    $result->setCollectionName('input_filter');
                    $result->getLinks()->add(Link::factory(array(
                        'rel' => 'self',
                        'route' => array(
                            'name' => $route,
                            'params' => array(
                                'name'                    => $module,
                                'controller_service_name' => str_replace('\\', '-', $controller),
                            ),
                        ),
                    )));
                    $result->setEntityRoute($route);
                    break;
                }

                $name   = $result['input_filter_name'];
                $result = new HalEntity($result, $name);
                $this->injectEntitySelfLink($result->getLinks(), $route, $module, $controller, $name);
                break;

            case $request::METHOD_POST:
                if ($inputFilterName) {
                    return new ApiProblemResponse(
                        new ApiProblem(400, 'POST requests are not allowed to individual input filters')
                    );
                }
                // Intentionally not breaking, as remainder of logic remains the same as PUT

            case $request::METHOD_PUT:
                $inputFilter = $this->bodyParams();
                $result = $this->model->update($module, $controller, $inputFilter);
                if (!$result) {
                    return new ApiProblemResponse(
                        new ApiProblem(
                            500,
                            'There was an unexpected error updating the input filter;'
                            . ' please verify the module and controller specified are valid'
                        )
                    );
                }

                $name   = $result['input_filter_name'];
                $result = new HalEntity($result, $name);
                $this->injectEntitySelfLink($result->getLinks(), $route, $module, $controller, $name);
                break;

            case $request::METHOD_DELETE:
                if (empty($inputFilterName)) {
                    return new ApiProblemResponse(
                        new ApiProblem(400, 'The input filter name has not been specified')
                    );
                }

                $result = $this->model->remove($module, $controller, $inputFilterName);
                if (!$result) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input filter specified does not exist')
                    );
                }
                return $this->getResponse()->setStatusCode(204);
        }

        $e = $this->getEvent();
        $e->setParam('ZFContentNegotiationFallback', 'HalJson');

        return new ViewModel(array('payload' => $result));
    }

    /**
     * Remove the key from the input filter array
     *
     * @param  array $inputFilter
     * @return array
     */
    protected function removeKey($inputFilter)
    {
        $result = array();
        foreach ($inputFilter as $key => $value) {
            $result[] = $value;
        }
        return $result;
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

    protected function deriveRouteName($route)
    {
        $matches = array();
        preg_match('/(?P<type>rpc|rest)/', $route, $matches);
        return sprintf('zf-apigility/api/module/%s-service/input-filter', $matches['type']);
    }

    public function injectEntitySelfLink($links, $route, $module, $controller, $inputFilterName)
    {
        $links->add(Link::factory(array(
            'rel' => 'self',
            'route' => array(
                'name' => $route,
                'params' => array(
                    'name'                    => $module,
                    'controller_service_name' => $controller,
                    'input_filter_name'       => $inputFilterName,
                ),
            ),
        )));
    }
}
