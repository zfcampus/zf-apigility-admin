<?php
/**
 * @license   http://opensource.org/licenses/BSD-2-Clause BSD-2-Clause
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\Apigility\Admin\Exception;
use ZF\Apigility\Admin\Model\VersioningModelFactory;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class VersioningController extends AbstractActionController
{
    protected $modelFactory;

    public function __construct(VersioningModelFactory $modelFactory)
    {
        $this->modelFactory = $modelFactory;
    }

    public function versioningAction()
    {
        $request = $this->getRequest();

        $module = $this->bodyParam('module', false);
        if (!$module) {
            return new ApiProblemModel(
                new ApiProblem(422, 'Module parameter not provided', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
            );
        }

        $model = $this->modelFactory->factory($module);

        $version = $this->bodyParam('version', false);
        if (!$version) {
            $versions = $model->getModuleVersions($module);
            if (!$versions) {
                return new ApiProblemModel(new ApiProblem(404, 'Module not found'));
            }
            sort($versions);
            $version = array_pop($versions);
            $version += 1;
        }


        try {
            $result = $model->createVersion($module, $version);
        } catch (Exception\InvalidArgumentException $ex) {
            return new ApiProblemModel(
                new ApiProblem(422, 'Invalid module and/or version', 'https://tools.ietf.org/html/rfc4918', 'Unprocessable Entity')
            );
        }

        return array(
            'success' => true,
            'version' => $version,
        );
    }
}
