<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class DocumentationController extends AbstractActionController
{
    protected $model;

    public function __construct(DocumentationModel $docModel)
    {
        $this->model = $docModel;
    }

    public function indexAction()
    {
        $event           = $this->getEvent();
        $routeMatch      = $event->getRouteMatch();
        $route           = $this->deriveRouteName($routeMatch->getMatchedRouteName());
        $request         = $this->getRequest();
        $module          = $this->params()->fromRoute('name', false);
        $controller      = $this->params()->fromRoute('controller_service_name', false);
        $method          = $this->params()->fromRoute('method', false);
        $section         = $this->params()->fromRoute('section', false);

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
                if ($method && $section) {
                    $result = array('documentation' => $this->model->fetchControllerMethodDocumentation($module, $controller, $method, $section));
                } else {
                    $result = array('documentation' => $this->model->fetchControllerDocumentation($module, $controller));
                }
                break;
            case $request::METHOD_PUT:
                $body = $this->bodyParams();

                if ($method && $section) {
                    $this->model->storeControllerMethodDocumentation($module, $controller, $method, $section, $body['documentation']);
                    $result = array('documentation' => $this->model->fetchControllerMethodDocumentation($module, $controller, $method, $section));
                } else {
                    $this->model->storeControllerDocumentation($module, $controller, $body['documentation']);
                    $result = array('documentation' => $this->model->fetchControllerDocumentation($module, $controller));
                }

                $result = $this->model->update($module, $controller, $method, $section, $body['documentation']);
                break;
//            case $request::METHOD_DELETE:
//                $result = $this->model->remove($module, $controller, $method, $section);
//                break;
        }

        var_dump($result); exit;

//        $e = $this->getEvent();
//        $e->setParam('ZFContentNegotiationFallback', 'HalJson');
//
//        $viewModel = new ViewModel(['payload' => $result]);
//        $viewModel->setTerminal(true);
//        return $viewModel;
    }

    protected function deriveRouteName($route)
    {
        $matches = [];
        preg_match('/(?P<type>rpc|rest)/', $route, $matches);
        return sprintf('zf-apigility-admin/api/module/%s-service/%s-doc', $matches['type'], $matches['type']);
    }
} 