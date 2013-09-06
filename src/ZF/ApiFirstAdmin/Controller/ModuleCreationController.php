<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiFirstAdmin\Controller;

use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiFirstAdmin\Model\ModuleModel;
use ZF\ApiFirstAdmin\Model\Module;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Resource;
use ZF\Hal\Link\Link;

class ModuleCreationController extends AbstractActionController
{
    protected $moduleModel;

    public function __construct(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    public function apiEnableAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {

            case $request::METHOD_PUT:
                $module = $this->bodyParam('module', false);
                if (!$module) {
                    return new ApiProblem(422, 'Module parameter not provided', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity');
                }

                $result = $this->moduleModel->updateModule($module);

                if (!$result) {
                    return new ApiProblem(500, 'Unable to API-First-enable the module');
                }

                $metadata = new Module($module);
                $resource = new Resource($metadata, $module);
                $resource->getLinks()->add(Link::factory(array(
                    'rel'   => 'self',
                    'route' => array(
                        'name'   => 'zf-api-first-admin/api/module',
                        'params' => array('module' => $module),
                    ),
                )));
                $model    = new ViewModel(array('payload' => $resource));
                $model->setTerminal(true);
                return $model;

            default:
                return new ApiProblemModel(
                    new ApiProblem(405, 'Only the method PUT is allowed for this URI')
                );
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
}
