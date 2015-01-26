<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
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

    public function defaultVersionAction()
    {
        $module = $this->bodyParam('module', false);
        if (!$module) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Module parameter not provided',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $version = $this->bodyParam('version', false);

        if (!$version || !is_numeric($version)) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Missing or invalid version',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $model = $this->modelFactory->factory($module);

        if ($model->setDefaultVersion($version)) {
            return array('success' => true, 'version' => $version);
        } else {
            return new ApiProblemModel(
                new ApiProblem(500, 'An unexpected error occurred while attempting to set the default version')
            );
        }
    }

    public function versioningAction()
    {
        $request = $this->getRequest();

        $module = $this->bodyParam('module', false);
        if (!$module) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Module parameter not provided',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        $model = $this->modelFactory->factory($module);

        $version = $this->bodyParam('version', false);
        if (!$version) {
            try {
                $versions = $model->getModuleVersions($module);
            } catch (Exception\ExceptionInterface $ex) {
                return new ApiProblemModel(new ApiProblem(404, 'Module not found'));
            }
            if (!$versions) {
                return new ApiProblemModel(new ApiProblem(500, 'Module cannot be versioned'));
            }
            sort($versions);
            $version = array_pop($versions);
            $version += 1;
        }


        try {
            $result = $model->createVersion($module, $version);
        } catch (Exception\InvalidArgumentException $ex) {
            return new ApiProblemModel(
                new ApiProblem(
                    422,
                    'Invalid module and/or version',
                    'https://tools.ietf.org/html/rfc4918',
                    'Unprocessable Entity'
                )
            );
        }

        return array(
            'success' => true,
            'version' => $version,
        );
    }
}
