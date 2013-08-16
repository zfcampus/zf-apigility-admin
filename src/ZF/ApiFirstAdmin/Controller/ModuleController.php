<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\ApiFirstAdmin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;
use ZF\ApiFirstAdmin\Model\ApiFirstModule;
use Zend\Http\Request;

class ModuleController extends AbstractActionController
{
    protected $moduleResource;

    public function __construct(ApiFirstModule $moduleResource)
    {
        $this->moduleResource = $moduleResource;
    }
    
    public function processAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {

            case $request::METHOD_POST:
                $params = json_decode($request->getContent(), true); 
                $result = false;
                if (isset($params['module'])) {
                    $result = $this->moduleResource->createModule($params['module']);
                }
                $return = array();
                if ($result) {
                    $return = array(
                        'module' => $params['module']
                    );
                }
                return $return;
           
            default:
                return new ApiProblemModel(
                    new ApiProblem(405, 'Only the method GET and POST are allowed for this URI')
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
