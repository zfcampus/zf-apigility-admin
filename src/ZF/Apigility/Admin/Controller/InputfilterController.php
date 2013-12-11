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

class InputfilterController extends AbstractActionController
{
    protected $model;

    public function __construct(InputfilterModel $model)
    {
        $this->model = $model;
    }

    public function indexAction()
    {
        $request = $this->getRequest();

        $module     = $this->params()->fromRoute('module', null);
        $controller = $this->params()->fromRoute('controller', null);
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

        $inputfilter = $this->model->fetch($module, $controller, $inputname);

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                if (!$inputfilter) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input filter specified doesn\'t exist')
                    );
                }
                break;
            case $request::METHOD_POST:
                break;
            case $request::METHOD_PATCH:
                if (!$inputfilter) {
                    return new ApiProblemResponse(
                        new ApiProblem(404, 'The input filter specified doesn\'t exist')
                    );
                }

                break;
        }

        $model = new JsonModel(['input_filter' => $inputfilter]);
        $model->setTerminal(true);
        return $model;
    }
}
