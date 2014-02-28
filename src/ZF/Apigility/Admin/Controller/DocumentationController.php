<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request as HttpRequest;
use ZF\Apigility\Admin\Model\DocumentationModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Link\Link as HalLink;
use ZF\Hal\Entity as HalEntity;

class DocumentationController extends AbstractActionController
{
    protected $model;

    public function __construct(DocumentationModel $docModel)
    {
        $this->model = $docModel;
    }

    public function indexAction()
    {
        /** @var HttpRequest $request */
        $request = $this->getRequest();
        $httpMethod = $request->getMethod();
        $module = $this->params()->fromRoute('name', false);
        $controllerServiceName = $this->params()->fromRoute('controller_service_name', false);
        $controllerType = $this->params()->fromRoute('controller_type'); // rest or rpc

        $routeName = $this->deriveRouteName($this->getEvent()->getRouteMatch()->getMatchedRouteName());

        switch ($httpMethod) {
            case HttpRequest::METHOD_GET:
                $result = new HalEntity(
                    $this->model->fetchDocumentation($module, $controllerServiceName),
                    'documentation'
                );
                $result->getLinks()->add((new HalLink('self'))->setRoute($routeName));
                break;
            case HttpRequest::METHOD_PUT:
                $documentation = $this->bodyParams();
                $result = new HalEntity(
                    $this->model->storeDocumentation($module, $controllerType, $controllerServiceName, $documentation, true),
                    'documentation'
                );
                $result->getLinks()->add((new HalLink('self'))->setRoute($routeName));
                break;
            case HttpRequest::METHOD_PATCH:
                $documentation = $this->bodyParams();
                $result = new HalEntity(
                    $this->model->storeDocumentation($module, $controllerType, $controllerServiceName, $documentation, false),
                    'documentation'
                );
                $result->getLinks()->add((new HalLink('self'))->setRoute($routeName));
                break;
            case HttpRequest::METHOD_DELETE:
            case HttpRequest::METHOD_POST:
            default:
                return new ApiProblemResponse(
                    new ApiProblem(404, 'Unsupported method.')
                );
        }

        $e = $this->getEvent();
        $e->setParam('ZFContentNegotiationFallback', 'HalJson');

        $viewModel = new ViewModel(['payload' => $result]);
        $viewModel->setTerminal(true);
        return $viewModel;
    }

    protected function deriveRouteName($route)
    {
        $matches = [];
        preg_match('/(?P<type>rpc|rest)/', $route, $matches);
        return sprintf('zf-apigility/api/module/%s-service/doc', $matches['type']);
    }

}
