<?php

namespace ZF\Apigility\Admin\Model;

use \InvalidArgumentException;
use ZF\Configuration\ModuleUtils;

class ModulePathSpec
{
    protected $modules;

    protected $modulePathSpec = "%s/module/%s";

    protected $psrSpecs = array(
        'psr-0' => '%modulePath%/src/%moduleName%',
        'psr-4' => '%modulePath%/src'
    );

    /**
     * @var string  PSR-0
     */
    protected $moduleSourcePathSpec;

    protected $restPathSpec = "/V%version%/Rest";

    protected $rpcPathSpec = "/V%version%/Rpc";

    protected $applicationPath = '.';

    /**
     * @param ModuleUtils $modules
     * @param string $sourcePathSpec
     * @param string $modulePath
     */
    public function __construct(ModuleUtils $modules, $sourcePathSpec = 'psr-0',  $applicationPath = ".")
    {
        if(!array_key_exists($sourcePathSpec, $this->psrSpecs)) {
            throw new InvalidArgumentException("Invalid sourcePathSpec valid values are psr-0, psr-4");
        }

        $this->modules              = $modules;
        $this->moduleSourcePathSpec = $this->psrSpecs[$sourcePathSpec];
        $this->applicationPath      = $applicationPath;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setApplicationPath($path)
    {
        $this->applicationPath = $path;

        return $this;
    }

    public function getApplicationPath()
    {
        return $this->applicationPath;
    }

    public function getModulePath($moduleName)
    {
        // see if we can get the path from ModuleUtils, if module isn't set will throw exception
        try {
            $modulePath = $this->modules->getModulePath($moduleName);
        } catch(\Exception $e) {
            $modulePath = sprintf($this->modulePathSpec, $this->applicationPath, $moduleName);
        }

        return $modulePath;
    }

    /**
     * @todo add support for custom rest/rpc path specs.  This will require that className resolution is also provided.
    public function setRestPathSpec($spec)
    {
        if(empty($spec)) {
            return;
        }

        $this->restPathSpec = $spec;

        return $this;
    }

    public function setRpcPathSpec($spec)
    {
        if(empty($spec)) {
            return;
        }

        $this->rpcPathSpec = $spec;

        return $this;
    }*/

    public function getModuleSourcePath($moduleName, $fullPath = true)
    {
        $find    = array("%modulePath%", "%moduleName%");

        if(true === $fullPath) {
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

        if(substr($path, -1) != "/") {
            $path .= "/";
        }

        $path .= (!empty($serviceName)) ? str_replace("\\", "/", $serviceName) : '';

        return $path;
    }

    public function getRpcPath($moduleName, $version = 1, $serviceName = null)
    {
        $find    = array("\\", "%version%");
        $replace = array("/", $version);

        $path = $this->getModuleSourcePath($moduleName);
        $path .= str_replace($find, $replace, $this->rpcPathSpec);

        if(substr($path, -1) != "/") {
            $path .= "/";
        }

        $path .= (!empty($serviceName)) ? str_replace("\\", "/", $serviceName) : '';

        return $path;
    }

    public function getModuleConfigPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/config";
    }

    public function getModuleConfigFilePath($moduleName)
    {
        return $this->getModuleConfigPath($moduleName) . "/module.config.php";
    }

    public function getModuleViewPath($moduleName)
    {
        return $this->getModulePath($moduleName) . "/view";
    }
}
