<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiFirstAdmin\Controller;

use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiFirstAdmin\Model\ModuleModel;
use ZF\ApiFirstAdmin\Model\ModuleEntity;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ContentNegotiation\ViewModel;
use ZF\Hal\Resource;
use ZF\Hal\Link\Link;
use ReflectionClass;

class SourceController extends AbstractActionController
{
    protected $moduleModel;

    public function __construct(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    public function sourceAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {

            case $request::METHOD_GET:
                $module = urldecode($this->params()->fromQuery('module', false));
                if (!$module) {
                    return new ApiProblemModel(
                        new ApiProblem(422, 'Module parameter not provided', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
                    );
                }
                $result = $this->moduleModel->getModule($module);
                if (!$result) {
                    return new ApiProblemModel(
                        new ApiProblem(422, 'The module specified doesn\'t exist', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
                    );
                }
   
                $class = urldecode($this->params()->fromQuery('class', false));
                if (!$class) {
                    return new ApiProblemModel(
                        new ApiProblem(422, 'Class parameter not provided', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
                    );
                }
                if (!class_exists($class)) {
                    return new ApiProblemModel(
                        new ApiProblem(422, 'The class specified doesn\'t exist', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
                    );
                }

                $reflector = new ReflectionClass($class);
                $fileName = $reflector->getFileName();
                
                $metadata = array(
                    'module' => $module,
                    'class'  => $class,
                    'file'   => $fileName, 
                    'source' => highlight_file($fileName, true)
                );

                $model = new ViewModel($metadata);
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
