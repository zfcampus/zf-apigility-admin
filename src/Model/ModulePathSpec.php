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
     * PSR-4 path spec key
     */
    const PSR_4 = 'psr-4';

    /**
     * PSR-0 path spec key
     */
    const PSR_0 = 'psr-0';

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
    protected $psrSpecs = [
        self::PSR_0 => '%modulePath%/src/%moduleName%',
        self::PSR_4 => '%modulePath%/src',
    ];

    /**
     * @var string
     */
    protected $currentSpec = self::PSR_0;

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
     * @param string $applicationPath
     */
    public function __construct(ModuleUtils $modules, $sourcePathSpec = self::PSR_0, $applicationPath = ".")
    {
        $sourcePathSpec = strtolower($sourcePathSpec);

        if (! array_key_exists($sourcePathSpec, $this->psrSpecs)) {
            throw new InvalidArgumentException(sprintf(
                "Invalid sourcePathSpec. Valid values are %s and %s",
                self::PSR_0,
                self::PSR_4
            ));
        }

        $this->modules              = $modules;
        $this->moduleSourcePathSpec = $this->psrSpecs[$sourcePathSpec];
        $this->applicationPath      = $this->normalizePath($applicationPath);
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
        $this->applicationPath = $this->normalizePath($path);

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
     * @param string $moduleName
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

        return $this->normalizePath($modulePath);
    }

    /**
     * Returns the source path for the module that is specified
     *
     * @param string $moduleName
     * @param bool $fullPath
     * @return string
     */
    public function getModuleSourcePath($moduleName, $fullPath = true)
    {
        $find = ["%modulePath%", "%moduleName%"];

        if (true === $fullPath) {
            $replace = [$this->getModulePath($moduleName), $moduleName];
        } else {
            $replace = ['', $moduleName];
        }

        foreach ($this->psrSpecs as $psr => $pathSpec) {
            $path = $this->normalizePath(str_replace($find, $replace, $pathSpec));

            if (is_dir($path) && file_exists($path . '/Module.php')) {
                $this->currentSpec = $psr;
                $this->moduleSourcePathSpec = $pathSpec;
                return $path;
            }
        }

        $moduleSourcePath = str_replace($find, $replace, $this->moduleSourcePathSpec);
        return $this->normalizePath($moduleSourcePath);
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
        $find    = ["\\", "%serviceName%", "%version%"];
        $replace = ["/", $serviceName, $version];

        $path = $this->getModuleSourcePath($moduleName);
        $path .= str_replace($find, $replace, $this->restPathSpec);

        if (substr($path, -1) != "/") {
            $path .= "/";
        }

        if (! empty($serviceName)) {
            $path .= $serviceName;
        }

        return $this->normalizePath($path);
    }

    /**
     * @param string $moduleName
     * @param int $version
     * @param string $serviceName
     * @return string
     */
    public function getRpcPath($moduleName, $version = 1, $serviceName = null)
    {
        $find    = ["\\", "%version%"];
        $replace = ["/", $version];

        $path = $this->getModuleSourcePath($moduleName);
        $path .= str_replace($find, $replace, $this->rpcPathSpec);

        if (substr($path, -1) != "/") {
            $path .= "/";
        }

        if (! empty($serviceName)) {
            $path .= $serviceName;
        }

        return $this->normalizePath($path);
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleConfigPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/config";
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleConfigFilePath($moduleName)
    {
        return $this->getModuleConfigPath($moduleName) . "/module.config.php";
    }

    /**
     * @param string $moduleName
     * @return string
     */
    public function getModuleViewPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/view";
    }

    /**
     * Normalizes a path by converting back-slashes into normal slashes. This function should always remain idempotent.
     *
     * @param string $path
     * @return string
     */
    public function normalizePath($path)
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Normalizes a module name by converting periods and forward slashes into backslashes (for namespaces). This
     * function should always remain idempotent.
     *
     * @param string $moduleName
     * @return string
     */
    public function normalizeModuleName($moduleName)
    {
        return str_replace(['.', '/'], '\\', $moduleName);
    }
}
