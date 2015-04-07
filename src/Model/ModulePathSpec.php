<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use InvalidArgumentException;
use ZF\Configuration\ModuleUtils;

/**
 * Class ModulePathSpec
 *
 * Adds PSR-0 and PSR-4 support to Apigility.
 *
 * @package ZF\Apigility\Admin\Model
 */
class ModulePathSpec
{
    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @var string
     */
    protected $modulePathSpec = "%s/module/%s";

    /**
     * @var array
     */
    protected $psrSpecs = array(
        'psr-0' => '%modulePath%/src/%moduleName%',
        'psr-4' => '%modulePath%/src'
    );

    /**
     * @var string
     */
    protected $currentSpec = 'psr-0';

    /**
     * @var string  PSR-0
     */
    protected $moduleSourcePathSpec;

    /**
     * @var string
     */
    protected $restPathSpec = "/V%version%/Rest";

    /**
     * @var string
     */
    protected $rpcPathSpec = "/V%version%/Rpc";

    /**
     * @var string
     */
    protected $applicationPath = '.';

    /**
     * @param ModuleUtils $modules
     * @param string $sourcePathSpec
     * @param string $modulePath
     */
    public function __construct(ModuleUtils $modules, $sourcePathSpec = 'psr-0', $applicationPath = ".")
    {
        $sourcePathSpec = strtolower($sourcePathSpec);

        if (!array_key_exists($sourcePathSpec, $this->psrSpecs)) {
            throw new InvalidArgumentException("Invalid sourcePathSpec valid values are psr-0, psr-4");
        }

        $this->modules              = $modules;
        $this->moduleSourcePathSpec = $this->psrSpecs[$sourcePathSpec];
        $this->applicationPath      = $applicationPath;
        $this->currentSpec          = $sourcePathSpec;
    }

    /**
     * Returns the current path spec being utitlized. IE> psr-0 or psr-4
     *
     * @return string
     */
    public function getPathSpec()
    {
        return $this->currentSpec;
    }

    /**
     * Set the path to the application directory
     *
     * @param string $path
     * @return $this
     */
    public function setApplicationPath($path)
    {
        $this->applicationPath = $path;

        return $this;
    }

    /**
     * Get the path of the application directory
     *
     * @return string
     */
    public function getApplicationPath()
    {
        return $this->applicationPath;
    }

    /**
     * Returns the path for the module name that is specified.
     *
     * @param $moduleName
     * @return string
     */
    public function getModulePath($moduleName)
    {
        // see if we can get the path from ModuleUtils, if module isn't set will throw exception
        try {
            $modulePath = $this->modules->getModulePath($moduleName);
        } catch (\Exception $e) {
            $modulePath = sprintf($this->modulePathSpec, $this->applicationPath, $moduleName);
        }

        return $modulePath;
    }

    /**
     * Returns the source path for the module that is specified
     *
     * @param $moduleName
     * @param bool $fullPath
     * @return mixed
     */
    public function getModuleSourcePath($moduleName, $fullPath = true)
    {
        $find    = array("%modulePath%", "%moduleName%");

        $moduleName = str_replace('\\', '/', $moduleName);

        if (true === $fullPath) {
            $replace = array($this->getModulePath($moduleName), $moduleName);
        } else {
            $replace = array('', $moduleName);
        }

        return str_replace($find, $replace, $this->moduleSourcePathSpec);
    }

    /**
     * Get the REST service path for a given module, service name and version
     *
     * @param string $moduleName
     * @param string $serviceName
     * @param int $version
     * @return string
     */
    public function getRestPath($moduleName, $version = 1, $serviceName = null)
    {
        $find    = array("\\", "%serviceName%", "%version%");
        $replace = array("/", $serviceName, $version);

        $path = $this->getModuleSourcePath($moduleName);
        $path .= str_replace($find, $replace, $this->restPathSpec);

        if (substr($path, -1) != "/") {
            $path .= "/";
        }

        $path .= (!empty($serviceName)) ? str_replace("\\", "/", $serviceName) : '';

        return $path;
    }

    /**
     * @param $moduleName
     * @param int $version
     * @param null $serviceName
     * @return mixed|string
     */
    public function getRpcPath($moduleName, $version = 1, $serviceName = null)
    {
        $find    = array("\\", "%version%");
        $replace = array("/", $version);

        $path = $this->getModuleSourcePath($moduleName);
        $path .= str_replace($find, $replace, $this->rpcPathSpec);

        if (substr($path, -1) != "/") {
            $path .= "/";
        }

        $path .= (!empty($serviceName)) ? str_replace("\\", "/", $serviceName) : '';

        return $path;
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function getModuleConfigPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/config";
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function getModuleConfigFilePath($moduleName)
    {
        return $this->getModuleConfigPath($moduleName) . "/module.config.php";
    }

    /**
     * @param $moduleName
     * @return string
     */
    public function getModuleViewPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/view";
    }
}
