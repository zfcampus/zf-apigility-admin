<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Http\Request;
use Zend\Http\Response\Stream;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;

class PackageController extends AbstractActionController
{
    /**
     * Filename of a package retrieved over-the-wire.
     *
     * @var string
     */
    private $sentPackage;

    /**
     * @var string
     */
    private $zfdeployPath = 'vendor/zfcampus/zf-deploy/bin/zfdeploy.php';

    /**
     * @param null|string $zfdeployPath Path to use to zfdeploy.php.
     */
    public function __construct($zfdeployPath = null)
    {
        if (! empty($zfdeployPath) && is_string($zfdeployPath)) {
            $this->zfdeployPath = $zfdeployPath;
        }
    }

    /**
     * Handle incoming requests
     *
     * @return array|\Zend\Http\Response|ApiProblemResponse
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                return $this->fetch(
                    $this->params()->fromQuery('token', false),
                    $this->params()->fromQuery('format', false),
                    $this->getResponse()
                );

            case $request::METHOD_POST:
                return $this->create(
                    $this->bodyParam('format', false),
                    $this->bodyParams()
                );

            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the method POST is allowed for this URI')
                );
        }
    }

    /**
     * Fetch a generated package.
     *
     * @param string $fileId
     * @param string $format
     * @param \Zend\Http\Response $response
     * @return \Zend\Http\Response
     */
    private function fetch($fileId, $format, $response)
    {
        if (! $fileId || ! $format) {
            $response->setStatusCode(404);
            return $response;
        }

        $package  = $this->getPackageFile($fileId, $format);

        if (! file_exists($package)) {
            $response->setStatusCode(404);
            return $response;
        }

        $stream = fopen($package, 'r');
        if (false === $stream) {
            $response->setStatusCode(500);
            return $response;
        }

        // Mark the package for deletion when the request spins down.
        $this->sentPackage = $package;

        // Create a streamable response.
        $response = new Stream();
        $response->setStream($stream);
        $response->getHeaders()
            ->addHeaderLine('Content-Type', 'application/octet-stream')
            ->addHeaderLine('Content-Disposition', sprintf(
                'attachment; filename="apigility_%s.%s"',
                date('Y-m-d_H-i-s'),
                $format
            ))
            ->addHeaderLine('Content-Length', filesize($package));

        return $response;
    }

    /**
     * Create a package, given a format and options.
     *
     * @param string $format
     * @param array $params
     * @return array|ApiProblemResponse
     */
    private function create($format, array $params)
    {
        if (! $format
            || ! is_string($format)
            || ! in_array(strtolower($format), array('zip', 'tar', 'tgz', 'zpk'))
        ) {
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
        $package  = $this->getPackageFile($fileId, $format);
        $cmd      = sprintf('php %s build %s', $this->zfdeployPath, $package);

        $apis = array_key_exists('apis', $params) ? $params['apis'] : null;
        $cmd .= $this->createModulesOption($apis);

        $composer = array_key_exists('composer', $params) ? $params['composer'] : null;
        $cmd .= $this->createComposerOption($composer);

        $config = array_key_exists('config', $params) ? $params['config'] : null;
        $cmd .= $this->createConfigOption($config);

        if ($format === 'zpk') {
            $cmd .= $this->createZpkOptions($params);
        }

        // Execute zf-deploy
        shell_exec($cmd);

        if (! file_exists($package)) {
            return new ApiProblemResponse(
                new ApiProblem(
                    500,
                    'Unable to create package, or error creating package'
                )
            );
        }

        return array('token' => $fileId, 'format' => $format);
    }

    /**
     * Create the package file name.
     *
     * @param string $fileId
     * @param string $format
     * @return string
     */
    private function getPackageFile($fileId, $format)
    {
        return sys_get_temp_dir() . '/apigility_' . $fileId . '.' . strtolower($format);
    }

    /**
     * Create and return the --modules option, if any.
     *
     * @param null|array $apis
     * @return string
     */
    private function createModulesOption($apis)
    {
        if (! is_array($apis)) {
            return '';
        }

        $modules = array_map(
            function ($entry) {
                return substr($entry, 7);
            },
            glob('module/*', GLOB_ONLYDIR)
        );

        $toInclude = array();

        foreach ($modules as $mod) {
            if (! isset($apis[$mod]) || $apis[$mod]) {
                $toInclude[] = $mod;
            }
        }

        return ' --modules=' . escapeshellarg(implode(',', $toInclude));
    }

    /**
     * Create the composer option, if any.
     *
     * @param null|bool $composer
     * @return string
     */
    private function createComposerOption($composer)
    {
        if (null === $composer) {
            return '';
        }

        return (bool) $composer ? ' --composer=on' : ' --composer=off';
    }

    /**
     * Create the config option, if any.
     *
     * @param null|string $config
     * @return string
     */
    private function createConfigOption($config)
    {
        if (empty($config) || ! is_string($config)) {
            return '';
        }

        return ' --configs=' . escapeshellarg($config);
    }

    /**
     * Create and return any ZPK-specific options.
     *
     * @param array $params
     * @return string
     */
    private function createZpkOptions(array $params)
    {
        $options = '';

        $xml = array_key_exists('zpk_xml', $params) ? $params['zpk_xml'] : null;
        $options .= $this->createZpkXmlOption($xml);

        $assets = array_key_exists('zpk_assets', $params) ? $params['zpk_assets'] : null;
        $options .= $this->createZpkAssetsOption($assets);

        $version = array_key_exists('zpk_version', $params) ? $params['zpk_version'] : null;
        $options .= $this->createZpkVersionOption($version);

        return $options;
    }

    /**
     * Create and return the deploymentxml option.
     *
     * @param null|string $xml
     * @return string
     */
    private function createZpkXmlOption($xml)
    {
        if (null === $xml || ! is_string($xml)) {
            return '';
        }

        return ' --deploymentxml=' . escapeshellarg($xml);
    }

    /**
     * Create and return the zpkdata option.
     *
     * @param null|string $assets
     * @return string
     */
    private function createZpkAssetsOption($assets)
    {
        if (null === $assets || ! is_string($assets)) {
            return '';
        }

        return ' --zpkdata=' . escapeshellarg($assets);
    }

    /**
     * Create and return the version option.
     *
     * @param null|string $version
     * @return string
     */
    private function createZpkVersionOption($version)
    {
        if (null === $version || ! is_string($version)) {
            return '';
        }

        return ' --version=' . escapeshellarg($version);
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

    /**
     * Unlink any package sent over the wire previously.
     */
    public function __destruct()
    {
        if ($this->sentPackage && file_exists($this->sentPackage)) {
            unlink($this->sentPackage);
        }
    }
}
