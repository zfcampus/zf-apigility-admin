<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ReflectionClass;
use Zend\Filter\FilterChain;
use ZF\Apigility\Admin\Exception;
use ZF\Configuration\ConfigResource;

/**
 * Class VersioningModel
 *
 * @deprecated use \ZF\Apigility\Admin\Model\ModuleVersioningModel instead
 */
class VersioningModel
{
    protected $configResource;

    protected $docsConfigResource;

    protected $moduleNameFilter;

    private $pathSpec;

    /**
     * @param  ConfigResource $config
     * @param  null|ConfigResource $docsConfig
     * @param  ModulePathSpec $pathSpec
     * @deprecated
     */
    public function __construct(
        ConfigResource $config,
        ConfigResource $docsConfig = null,
        ModulePathSpec $pathSpec = null
    ) {
        $this->configResource = $config;
        $this->docsConfigResource = $docsConfig;
        $this->pathSpec = $pathSpec;
    }

    /**
     * getModuleVersioningModel
     * @param $name
     * @param null|string $srcPath Do not use this parameter unless you're providing for a transition to the new class
     *                          (see deprecation notice on this class)
     * @return ModuleVersioningModel
     */
    private function getModuleVersioningModel($name, $srcPath = null)
    {
        $name = $this->normalizeModule($name);
        $hasPathSpec = null !== $this->pathSpec;

        if ($hasPathSpec) {
            $pathSpecType = $this->pathSpec->getPathSpec();
            if (! $srcPath) {
                $srcPath = $this->pathSpec->getModuleSourcePath($name);
            }
            $configDirPath = $this->pathSpec->getModuleConfigPath($name);
        } else {
            $pathSpecType = ModulePathSpec::PSR_0;
            $srcPath = $this->getModuleSourcePath($name);
            $configDirPath = $this->locateConfigPath($srcPath);
        }

        return new ModuleVersioningModel(
            $name,
            $configDirPath,
            $srcPath,
            $this->configResource,
            $this->docsConfigResource,
            $pathSpecType
        );
    }

    /**
     * Create a new version for a module
     *
     * @param  string $module
     * @param  int $version
     * @param  bool|string $path
     * @return bool
     * @deprecated
     */
    public function createVersion($module, $version, $path = false)
    {
        return $this->getModuleVersioningModel($module, $path)
            ->createVersion($version);
    }

    /**
     * Get the versions of a module
     *
     * @param  string $module
     * @param  bool|string $path
     * @return array|bool
     * @deprecated
     */
    public function getModuleVersions($module, $path = false)
    {
        return $this->getModuleVersioningModel($module, $path)
            ->getModuleVersions();
    }

    /**
     * Updates the default version of a module that will be used if no version is
     * specified by the API consumer.
     *
     * @param  int $defaultVersion
     * @return bool
     * @deprecated
     */
    public function setDefaultVersion($defaultVersion)
    {
        // here we don't care about module name or path because the operation doesn't need it
        return (new ModuleVersioningModel('', __DIR__, __DIR__, $this->configResource))
            ->setDefaultVersion($defaultVersion);
    }

    /**
     * Normalize a module name
     *
     * Module names come over the wire dot-separated; make them namespaced.
     *
     * @param  string $module
     * @return string
     * @deprecated
     */
    protected function normalizeModule($module)
    {
        if ($this->pathSpec) {
            return $this->pathSpec->normalizeModuleName($module);
        }

        return str_replace(['.', '/'], '\\', $module);
    }

    /**
     * Determine the source path for the module
     *
     * Usually, this is the "src/{modulename}" subdirectory of the
     * module.
     *
     * @param string $module
     * @param bool $appendNamespace If true, it will append the module's namespace to the path - for PSR0 compatibility
     * @return string
     * @deprecated
     */
    protected function getModuleSourcePath($module, $appendNamespace = true)
    {
        // for clients that know how to instantiate this class with a ModulePathSpec
        if (null !== $this->pathSpec) {
            $path = $this->pathSpec->getModuleSourcePath($module);
            if ($this->pathSpec->getPathSpec() === 'psr-0') {
                $path .= DIRECTORY_SEPARATOR . $module;
            }
            return $path;
        }

        // .. or fall back to the old method, which only supports PSR-0
        $moduleClass = sprintf('%s\\Module', $module);

        if (! class_exists($moduleClass)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module %s doesn\'t exist',
                $module
            ));
        }

        $r       = new ReflectionClass($moduleClass);
        $srcPath = dirname($r->getFileName());
        if (file_exists($srcPath . '/src') && is_dir($srcPath . '/src')) {
            $parts = [$srcPath, 'src'];
            if ($appendNamespace) {
                $parts[] = str_replace('\\', '/', $moduleClass);
            }
            $srcPath = implode(DIRECTORY_SEPARATOR, $parts);
        } else {
            if (! $appendNamespace && substr($srcPath, - strlen($module)) == $module) {
                $srcPath = substr($srcPath, 0, strlen($srcPath) - strlen($module) - 1);
            }
        }

        if (! file_exists($srcPath) && ! is_dir($srcPath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module "%s" has a malformed directory structure; cannot determine source path',
                $module
            ));
        }

        return $srcPath;
    }

    /**
     * Locate the config path for this module
     *
     * @param  string $srcPath
     * @return string|false
     * @deprecated
     */
    protected function locateConfigPath($srcPath)
    {
        $config = sprintf('%s/config', $srcPath);
        if (file_exists($config) && is_dir($config)) {
            return $config;
        }

        if ($srcPath == '.' || $srcPath == '/') {
            return false;
        }

        return $this->locateConfigPath(dirname($srcPath));
    }

    /**
     * Filter for module names
     *
     * @return FilterChain
     * @deprecated
     */
    protected function getModuleNameFilter()
    {
        if ($this->moduleNameFilter instanceof FilterChain) {
            return $this->moduleNameFilter;
        }

        $this->moduleNameFilter = new FilterChain();
        $this->moduleNameFilter->attachByName('Word\CamelCaseToDash')
            ->attachByName('StringToLower');
        return $this->moduleNameFilter;
    }
}
