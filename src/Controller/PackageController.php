<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class PackageController extends AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                $fileId   = $this->params()->fromQuery('token');
                $format   = $this->params()->fromQuery('format');
                $response = $this->getResponse();

                if (!$fileId || !$format) {
                    $response->setStatusCode(404);
                    return $response;
                }
                $package  = sys_get_temp_dir() . '/apigility_' . $fileId . '.' . strtolower($format);
                $response = $this->getResponse();
                $headers  = $response->getHeaders();

                if (!file_exists($package)) {
                    $response->setStatusCode(404);
                    return $response;
                }
                $fileContents = file_get_contents($package);

                $response->setContent($fileContents);
                $headers->clearHeaders()
                        ->addHeaderLine('Content-Type', 'application/octet-stream')
                        ->addHeaderLine('Content-Disposition', 'attachment; filename="apigility_' .
                                        date("Y-m-d_H-i-s") . '.' . $format . '"')
                        ->addHeaderLine('Content-Length', strlen($fileContents));

                unlink($package);
                return $response;
                break;

            case $request::METHOD_POST:
                $format = $this->bodyParam('format', false);
                if (!$format || !in_array(strtolower($format), array('zip', 'tar', 'tgz', 'zpk'))) {
                    return new ApiProblemResponse(
                        new ApiProblem(
                            422,
                            'Format parameter not valid, we accept only zip, tar, tgz or zpk type',
                            'https://tools.ietf.org/html/rfc4918',
                            'Unprocessable Entity'
                        )
                    );
                }
                $format   = strtolower($format);
                $fileId   = uniqid();
                $package  = sys_get_temp_dir() . '/apigility_' . $fileId . '.' . $format;
                $cmd      = "php vendor/bin/zfdeploy.php build {$package}";

                $apis = $this->bodyParam('apis');
                if (!is_null($apis)) {
                    $modules = array_map(
                        function ($entry) {
                            return substr($entry, 7);
                        },
                        glob('module/*', GLOB_ONLYDIR)
                    );
                    $toInclude = array();
                    foreach ($modules as $mod) {
                        if (!isset($apis[$mod]) || $apis[$mod]) {
                            $toInclude[] = $mod;
                        }
                    }
                    $cmd .= ' --modules=' . escapeshellarg(implode(",", $toInclude));
                }
                if (!is_null($this->bodyParam('composer'))) {
                    $cmd .= $this->bodyParam('composer') ? ' --composer=on' : ' --composer=off';
                }
                $config = $this->bodyParam('config');
                if (!empty($config)) {
                    $cmd .= ' --configs=' . escapeshellarg($config);
                }
                if ($format === 'zpk') {
                    if (!is_null($this->bodyParam('zpk_xml'))) {
                        $cmd .= ' --deploymentxml=' . escapeshellarg($this->bodyParam('zpk_xml'));
                    }
                    if (!is_null($this->bodyParam('zpk_assets'))) {
                        $cmd .= ' --zpkdata=' . escapeshellarg($this->bodyParam('zpk_assets'));
                    }
                    if (!is_null($this->bodyParam('zpk_version'))) {
                        $cmd .= ' --version=' . escapeshellarg($this->bodyParam('zpk_version'));
                    }
                }

                // Execute zf-deploy
                shell_exec($cmd);
                if (file_exists($package)) {
                    return array('token' => $fileId, 'format' => $format);
                }
                return array();

                break;

            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the method POST is allowed for this URI')
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
