<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;

class DocumentationController extends AbstractActionController
{
    protected $model;

    public function __construct(DocumentationModel $docModel)
    {
        $this->model = $docModel;
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $module = $this->params()->fromRoute('name', false);
        $controllerServiceName = $this->params()->fromRoute('controller_service_name', false);
        $resourceType = $this->params()->fromRoute('rest_resource_type', false); // collection or entity
        $httpMethod = $this->params()->fromRoute('http_method', false); // GET, POST, etc
        $httpDirection = $this->params()->fromRoute('http_direction', false); // request or response
        $controllerType = $this->params()->fromRoute('controller_type'); // rest or rpc

        if ($controllerType == 'rest' && $resourceType != false) {
            if (!in_array($resourceType, ['collection', 'entity'])) {
                return new ApiProblemResponse(
                    new ApiProblem(404, 'The rest type specified must be one of collection or entity')
                );
            }
        }

        if ($httpDirection) {
            if (!in_array($httpDirection, ['request', 'response'])) {
                return new ApiProblemResponse(
                    new ApiProblem(404, 'The http direction specified must be one of request or response')
                );
            }
            $target = $httpDirection;
        } else {
            $target = DocumentationModel::TARGET_DESCRIPTION;
        }

        if (!$module || !$this->model->moduleExists($module)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The module specified does not exist')
            );
        }

        if (!$controllerServiceName || !$this->model->controllerExists($module, $controllerServiceName)) {
            return new ApiProblemResponse(
                new ApiProblem(404, 'The controller specified does not exist')
            );
        }

        $documentation = null;

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                $documentation = ($controllerType == 'rest')
                    ? $this->model->fetchRestDocumentation($module, $controllerServiceName, $resourceType, $httpMethod, $target)
                    : $this->model->fetchRpcDocumentation($module, $controllerServiceName, $httpMethod, $target);

                /*
                $metadata = new \ZF\Apigility\Admin\Model\DocumentationEntity($module);
                $halResource = new \ZF\Hal\Resource($metadata, $module);
                $halResource->getLinks()->add(\ZF\Hal\Link\Link::factory(array(
                    'rel'   => 'self',
                    'route' => array(
                        'name'   => 'zf-apigility-admin/api/...',
                        // 'params' => array('docmentation' => $module),
                    ),
                )));
                */

                break;
            case $request::METHOD_PUT:
                $body = $this->bodyParams();
                if (!isset($body['documentation'])) {
                    return new ApiProblemResponse(
                        new ApiProblem(400, 'A documentation key is required in the body of the request')
                    );
                }
                $documentation = ($controllerType == 'rest')
                    ? $this->model->storeRestDocumentation($body['documentation'], $module, $controllerServiceName, $resourceType, $httpMethod, $target)
                    : $this->model->storeRpcDocumentation($body['documentation'], $module, $controllerServiceName, $httpMethod, $target);
                break;
            case $request::METHOD_DELETE:
                $documentation = ($controllerType == 'rest')
                    ? $this->model->storeRestDocumentation(DocumentationModel::NULL_DESCRIPTION, $module, $controllerServiceName, $resourceType, $httpMethod, $target)
                    : $this->model->storeRpcDocumentation(DocumentationModel::NULL_DESCRIPTION, $module, $controllerServiceName, $httpMethod, $target);
                $result = null;
                break;
            default:
                return new ApiProblemResponse(
                    new ApiProblem(404, 'Unsupported method.')
                );

        }

        // needs to return HalResource / HalCollection


        // use payload => HalResource/HalCollection

        // rel link called up/parent

        $e = $this->getEvent();
        $e->setParam('ZFContentNegotiationFallback', 'HalJson');

        $viewModel = new ViewModel(['payload' => ['documentation' => (isset($halResource) ? $halResource : $documentation)]]);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

} 