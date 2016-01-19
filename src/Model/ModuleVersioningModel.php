<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\FilterChain;
use Zend\Stdlib\Glob;
use ZF\Apigility\Admin\Exception;
use ZF\Configuration\ConfigResource;

/**
 * Allows management of module versions
 *
 *
 * @author Gabriel Somoza <gabriel@somoza.me>
 */
final class ModuleVersioningModel
{
    /** Regex to extract module versions from a module's source path */
    const REGEX_VERSION_DIR = '#V(?P<version>\d+)$#';

    const PATH_SPEC_PSR_0 = 'psr-0';
    const PATH_SPEC_PSR_4 = 'psr-4';

    /** @var string */
    private $moduleName;

    /** @var string */
    private $configDirPath;

    /** @var string */
    private $versionsPath;

    /** @var string */
    private $pathSpecType;

    /** @var ConfigResource */
    protected $configResource;

    /** @var null|ConfigResource */
    protected $docsConfigResource;

    /** @var FilterChain */
    protected $moduleNameFilter;

    /**
     * @param string $moduleName Name of the module.
     * @param string $configDirPath Path the the configuration folder, with one or more *.config.php files.
     * @param string $srcPath Path to the module's source folder for versions, resources & collections.
     * @param ConfigResource $config
     * @param null|ConfigResource $docsConfig
     * @param null|string $pathSpecType Whether the module uses a PSR-0 directory structure or not.
     *                                  Defaults to VersioningModel::PATH_SPEC_PSR_0.
     */
    public function __construct(
        $moduleName,
        $configDirPath,
        $srcPath,
        ConfigResource $config,
        ConfigResource $docsConfig = null,
        $pathSpecType = null
    ) {
        $this->moduleName = $this->normalizeModule((string) $moduleName);
        $this->configResource = $config;
        $this->docsConfigResource = $docsConfig;

        if (null === $pathSpecType) {
            $pathSpecType = self::PATH_SPEC_PSR_0;
        }
        $this->setPathSpecType($pathSpecType);
        $this->setConfigDirPath($configDirPath);
        $this->setVersionsPath($srcPath);
    }

    /**
     * Create a new version for a module
     *
     * @param  integer $version
     * @return bool
     */
    public function createVersion($version)
    {
        $versions = $this->getModuleVersions();
        if (in_array($version, $versions)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The API version %d of the module %s already exists',
                $version,
                $this->moduleName
            ));
        }

        $previous = (int) $version - 1;
        if (!in_array($previous, $versions)) {
            throw new Exception\RuntimeException(sprintf(
                'The previous API version %d doesn\'t exist, I cannot create version %d',
                $previous,
                $version
            ));
        }

        $this->recursiveCopy(
            $this->versionsPath . DIRECTORY_SEPARATOR . 'V'. $previous,
            $this->versionsPath . DIRECTORY_SEPARATOR . 'V' . $version,
            $previous,
            $version
        );

        foreach (Glob::glob($this->configDirPath . DIRECTORY_SEPARATOR . '*.config.php') as $file) {
            $this->updateConfigVersion($file, $previous, $version);
        }

        return true;
    }

    /**
     * Get the versions of a module
     *
     * @return array|bool
     */
    public function getModuleVersions()
    {
        $versions  = [];
        foreach (Glob::glob($this->versionsPath . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match(self::REGEX_VERSION_DIR, $dir, $matches)) {
                $versions[] = (int) $matches['version'];
            }
        }
        return $versions;
    }

    /**
     * Updates the default version of a module that will be used if no version is
     * specified by the API consumer.
     *
     * @param  integer $defaultVersion
     * @return boolean
     */
    public function setDefaultVersion($defaultVersion)
    {
        $defaultVersion = (int) $defaultVersion;

        $this->configResource->patch([
            'zf-versioning' => [
                'default_version' => $defaultVersion
            ]
        ], true);

        $config = $this->configResource->fetch(true);

        return isset($config['zf-versioning']['default_version'])
        && ($config['zf-versioning']['default_version'] === $defaultVersion);
    }

    /**
     * Copy file and folder recursively
     *
     * @param string $source
     * @param string $target
     * @param int $previous
     * @param int $version
     */
    protected function recursiveCopy($source, $target, $previous, $version)
    {
        $dir = opendir($source);
        @mkdir($target);
        $nsSep   = preg_quote('\\');
        $pattern = sprintf(
            '#%sV%s%s#',
            $nsSep,
            $previous,
            $nsSep
        );
        while (false !== ($file = readdir($dir))) {
            if (($file == '.') || ($file == '..')) {
                continue;
            }

            $origin      = sprintf('%s/%s', $source, $file);
            $destination = sprintf('%s/%s', $target, $file);

            if (is_dir($origin)) {
                $this->recursiveCopy($origin, $destination, $previous, $version);
                continue;
            }

            $contents    = file_get_contents($origin);
            $newContents = preg_replace($pattern, '\V' . $version . '\\', $contents);
            file_put_contents($destination, $newContents);
        }
        closedir($dir);
    }


    /**
     * Update a PHP configuration file from $previous to $version version
     *
     * @param  string  $file
     * @param  integer $previous Previous version
     * @param  integer $version New version
     * @return boolean
     */
    protected function updateConfigVersion($file, $previous, $version)
    {
        $module = $this->moduleName;
        if (preg_match('#[/\\\\]documentation.config.php$#', $file)) {
            return $this->updateDocumentationVersion($previous, $version);
        }

        $config = $this->configResource->fetch(true);
        if (empty($config)) {
            return false;
        }

        // update zf-hal.metadata_map
        if (isset($config['zf-hal']['metadata_map'])) {
            $newValues = $this->changeVersionArray($config['zf-hal']['metadata_map'], $previous, $version);
            $this->configResource->patch([
                'zf-hal' => ['metadata_map' => $newValues]
            ], true);
        }

        // update zf-rpc
        if (isset($config['zf-rpc'])) {
            $newValues = $this->changeVersionArray($config['zf-rpc'], $previous, $version);
            $this->configResource->patch([
                'zf-rpc' => $newValues
            ], true);
        }

        // update zf-rest
        if (isset($config['zf-rest'])) {
            $newValues = $this->changeVersionArray($config['zf-rest'], $previous, $version);
            $this->configResource->patch([
                'zf-rest' => $newValues
            ], true);
        }

        // update zf-content-negotiation
        if (isset($config['zf-content-negotiation'])) {
            foreach (['controllers', 'accept_whitelist', 'content_type_whitelist'] as $key) {
                if (isset($config['zf-content-negotiation'][$key])) {
                    $newValues = $this->changeVersionArray(
                        $config['zf-content-negotiation'][$key],
                        $previous,
                        $version
                    );

                    // change version in mediatype
                    if (in_array($key, ['accept_whitelist', 'content_type_whitelist'])) {
                        foreach ($newValues as $k => $v) {
                            foreach ($v as $index => $mediatype) {
                                if (strstr($mediatype, '.v' . $previous . '+')) {
                                    $newValues[$k][$index] = 'application/vnd.'
                                        . $this->getModuleNameFilter()->filter($module)
                                        . '.v'
                                        . $version
                                        . '+json';
                                }
                            }
                        }
                    }

                    $this->configResource->patch([
                        'zf-content-negotiation' => [$key => $newValues]
                    ], true);
                }
            }
        }

        // update zf-mvc-auth
        if (isset($config['zf-mvc-auth']['authorization'])) {
            $newValues = $this->changeVersionArray($config['zf-mvc-auth']['authorization'], $previous, $version);
            $this->configResource->patch([
                'zf-mvc-auth' => ['authorization' => $newValues]
            ], true);
        }

        // update zf-content-validation and input_filter_specs
        if (isset($config['zf-content-validation'])) {
            $newValues = $this->changeVersionArray($config['zf-content-validation'], $previous, $version);
            $this->configResource->patch([
                'zf-content-validation' => $newValues
            ], true);
        }

        if (isset($config['input_filter_specs'])) {
            $newValues = $this->changeVersionArray($config['input_filter_specs'], $previous, $version);
            $this->configResource->patch([
                'input_filter_specs' => $newValues
            ], true);
        }

        // update zf-apigility
        if (isset($config['zf-apigility']['db-connected'])) {
            $newValues = $this->changeVersionArray($config['zf-apigility']['db-connected'], $previous, $version);
            $this->configResource->patch([
                'zf-apigility' => ['db-connected' => $newValues]
            ], true);
        }

        // update service_manager
        if (isset($config['service_manager'])) {
            $newValues = $this->changeVersionArray($config['service_manager'], $previous, $version);
            $this->configResource->patch([
                'service_manager' => $newValues
            ], true);
        }

        // update controllers
        if (isset($config['controllers'])) {
            $newValues = $this->changeVersionArray($config['controllers'], $previous, $version);
            $this->configResource->patch([
                'controllers' => $newValues
            ], true);
        }

        return true;
    }

    /**
     * Change version in a namespace
     *
     * @param  string $string
     * @param  integer $previous
     * @param  integer $version
     * @return string
     */
    protected function changeVersionNamespace($string, $previous, $version)
    {
        return str_replace('\\V' . $previous . '\\', '\\V' . $version . '\\', $string);
    }

    /**
     * Change version in an array
     *
     * @param  array $data
     * @param  integer $previous
     * @param  integer $version
     * @return array
     */
    protected function changeVersionArray($data, $previous, $version)
    {
        $result = [];
        foreach ($data as $key => $value) {
            $newKey = $this->changeVersionNamespace($key, $previous, $version);
            if (is_array($value)) {
                $result[$newKey] = $this->changeVersionArray($value, $previous, $version);
            } else {
                $result[$newKey] = $this->changeVersionNamespace($value, $previous, $version);
            }
        }
        return $result;
    }

    /**
     * Normalize a module name
     *
     * Module names come over the wire dot-separated; make them namespaced.
     *
     * @param  string $module
     * @return string
     */
    protected function normalizeModule($module)
    {
        return str_replace(['.', '/'], '\\', $module);
    }

    /**
     * Filter for module names
     *
     * @return FilterChain
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

    /**
     * Update the documentation to add a new $version based on the $previous
     *
     * @param  integer $previous Previous version
     * @param  integer $version New version
     * @return true
     */
    protected function updateDocumentationVersion($previous, $version)
    {
        if (!$this->docsConfigResource) {
            // Nothing to do
            return true;
        }

        $originalDocs = $this->docsConfigResource->fetch(true);
        $newDocs = $this->changeVersionArray($originalDocs, $previous, $version);
        $this->docsConfigResource->patch($newDocs, true);
        return true;
    }

    /**
     * setPathSpecType
     * @param $pathSpecType
     * @return void
     */
    private function setPathSpecType($pathSpecType)
    {
        $pathSpecType = (string) $pathSpecType;
        if (!in_array($pathSpecType, [self::PATH_SPEC_PSR_0, self::PATH_SPEC_PSR_4])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid $setPathSpecType parameter supplied. Please use the PATH_SPEC_PSR_0 or PATH_SPEC_PSR_4 ' .
                'constants located in the %s class.',
                __CLASS__
            ));
        }
        $this->pathSpecType = $pathSpecType;
    }

    /**
     * Sets the path to the directory that contains each of the module's version. If the current module is a PSR-0
     * module then it automatically appends the module's namespace.
     *
     * @param string $srcPath The path to the root of the module.
     * @return void
     */
    private function setVersionsPath($srcPath)
    {
        $srcPath = (string) $srcPath;
        if ($this->pathSpecType == self::PATH_SPEC_PSR_0) {
            $srcPath .= DIRECTORY_SEPARATOR . $this->moduleName;
        }

        if (!file_exists($srcPath) || !is_dir($srcPath) || !is_writable($srcPath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Could not find source directory at path "%s". Make sure the directory exists and is writable.',
                $srcPath
            ));
        }

        $this->versionsPath = $srcPath;
    }

    /**
     * setConfigDirPath
     * @param $configDirPath
     * @return void
     */
    private function setConfigDirPath($configDirPath)
    {
        $configDirPath = (string)$configDirPath;
        if (!is_readable($configDirPath) || !is_dir($configDirPath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Could not find config directory at path "%s". Make sure the directory exists.',
                $configDirPath
            ));
        }
        $this->configDirPath = $configDirPath;
    }
}
