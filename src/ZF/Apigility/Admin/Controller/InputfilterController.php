<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use ZF\Apigility\Admin\Model\InputfilterModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use Zend\Http\Request;
use ZF\ContentNegotiation\ViewModel;

class InputfilterController extends AbstractActionController
{
    protected $model;

    public function __construct(InputfilterModel $model)
    {
        $this->model = $model;
    }

    public function indexAction()
    {
        $request    = $this->getRequest();
        $module     = $this->params()->fromRoute('name', null);
        $controller = $this->params()->fromRoute('controller_service_name', null);
        $inputname  = $this->params()->fromRoute('inputname', null);

        if (!$this->model->moduleExists($module)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The module specified doesn\'t exist')
            );
        }

        if (!$this->model->controllerExists($module, $controller)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The controller specified doesn\'t exist')
            );
        }

        switch ($request->getMethod()) {

            case $request::METHOD_GET:
                $inputfilter = $this->model->fetch($module, $controller, $inputname);
                if (false === $inputfilter) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input filter specified doesn\'t exist')
                    );
                }
                $result = array();
                if ($inputname && !empty($inputfilter)) {
                    $result = $inputfilter;
                } elseif (!empty($inputfilter)) {
                    $result = $this->removeKey($inputfilter);
                }
                break;

            case $request::METHOD_PUT:
                $inputfilter = $this->bodyParam('input_filters');
                if (!$inputfilter || !isset($inputfilter['name'])) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input_filters has not been specified or is not valid')
                    );
                }
                $inputfilter = [ $inputfilter['name'] => $inputfilter ];
                $result = $this->model->update($module, $controller, $inputfilter);
                if (!empty($result)) {
                    $validator = $result['zf-content-validation'][$controller]['input_filter'];
                    $result    = $this->removeKey($result['input_filters'][$validator]);
                }
                break;

            case $request::METHOD_DELETE:
                if (empty($inputname)) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The inputname has not been specified')
                    );
                }
                $result = $this->model->remove($module, $controller, $inputname);
                if (!$result) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The inputname specified doesn\'t exist')
                    );
                }
                return $this->getResponse()->setStatusCode(204);
                break;
        }

        return new ViewModel(['input_filters' => $result]);
    }

    /**
     * Remove the key from the input filter array
     *
     * @param  array $inputfilter
     * @return array
     */
    protected function removeKey($inputfilter)
    {
        $result = array();
        foreach ($inputfilter as $key => $value) {
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
}
