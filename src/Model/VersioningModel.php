<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ReflectionClass;
use Zend\Filter\FilterChain;
use Zend\Stdlib\Glob;
use ZF\Apigility\Admin\Exception;
use ZF\Configuration\ConfigResource;

class VersioningModel
{
    protected $configResource;

    protected $docsConfigResource;

    protected $moduleNameFilter;

    /**
     * @param  ConfigResource $config
     * @param  null|ConfigResource $docsConfig
     */
    public function __construct(ConfigResource $config, ConfigResource $docsConfig = null)
    {
        $this->configResource = $config;
        $this->docsConfigResource = $docsConfig;
    }

    /**
     * Create a new version for a module
     *
     * @param  string $module
     * @param  integer $version
     * @param  string $path
     * @return boolean
     */
    public function createVersion($module, $version, $path = false)
    {
        $module  = $this->normalizeModule($module);
        if (!$path) {
            $path = $this->getModuleSourcePath($module);
        }

        $versions = $this->getModuleVersions($module, $path);
        if (in_array($version, $versions)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The API version %d of the module %s already exists',
                $version,
                $module
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

        $this->recursiveCopy($path . '/V'. $previous, $path . '/V' . $version, $previous, $version);

        $configPath = $this->locateConfigPath($path);
        if ($configPath !== false) {
            foreach (Glob::glob($configPath . '/*.config.php') as $file) {
                $this->updateConfigVersion($module, $file, $previous, $version);
            }
        }

        return true;
    }

    /**
     * Get the versions of a module
     *
     * @param  string $module
     * @param  string $path
     * @return array|boolean
     */
    public function getModuleVersions($module, $path = false)
    {
        $module       = $this->normalizeModule($module);

        if (!$path) {
            $path = $this->getModuleSourcePath($module);
        }

        $versions  = array();
        foreach (Glob::glob($path . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match('/\\V(?P<version>\d+)$/', $dir, $matches)) {
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

        $this->configResource->patch(array(
            'zf-versioning' => array(
                'default_version' => $defaultVersion
            )
        ), true);

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
     * @param  string  $module
     * @param  string  $file
     * @param  integer $previous Previous version
     * @param  integer $version New version
     * @return boolean
     */
    protected function updateConfigVersion($module, $file, $previous, $version)
    {
        if (preg_match('#[/\\\\]documentation.config.php$#', $file)) {
            return $this->updateDocumentationVersion($module, $previous, $version);
        }

        $config = $this->configResource->fetch(true);
        if (empty($config)) {
            return false;
        }

        // update zf-hal.metadata_map
        if (isset($config['zf-hal']['metadata_map'])) {
            $newValues = $this->changeVersionArray($config['zf-hal']['metadata_map'], $previous, $version);
            $this->configResource->patch(array(
                'zf-hal' => array('metadata_map' => $newValues)
            ), true);
        }

        // update zf-rpc
        if (isset($config['zf-rpc'])) {
            $newValues = $this->changeVersionArray($config['zf-rpc'], $previous, $version);
            $this->configResource->patch(array(
                'zf-rpc' => $newValues
            ), true);
        }

        // update zf-rest
        if (isset($config['zf-rest'])) {
            $newValues = $this->changeVersionArray($config['zf-rest'], $previous, $version);
            $this->configResource->patch(array(
                'zf-rest' => $newValues
            ), true);
        }

        // update zf-content-negotiation
        if (isset($config['zf-content-negotiation'])) {
            foreach (array('controllers', 'accept_whitelist', 'content_type_whitelist') as $key) {
                if (isset($config['zf-content-negotiation'][$key])) {
                    $newValues = $this->changeVersionArray(
                        $config['zf-content-negotiation'][$key],
                        $previous,
                        $version
                    );

                    // change version in mediatype
                    if (in_array($key, array('accept_whitelist', 'content_type_whitelist'))) {
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

                    $this->configResource->patch(array(
                        'zf-content-negotiation' => array($key => $newValues)
                    ), true);
                }
            }
        }

        // update zf-mvc-auth
        if (isset($config['zf-mvc-auth']['authorization'])) {
            $newValues = $this->changeVersionArray($config['zf-mvc-auth']['authorization'], $previous, $version);
            $this->configResource->patch(array(
                'zf-mvc-auth' => array('authorization' => $newValues)
            ), true);
        }

        // update zf-content-validation and input_filter_specs
        if (isset($config['zf-content-validation'])) {
            $newValues = $this->changeVersionArray($config['zf-content-validation'], $previous, $version);
            $this->configResource->patch(array(
                'zf-content-validation' => $newValues
            ), true);
        }

        if (isset($config['input_filter_specs'])) {
            $newValues = $this->changeVersionArray($config['input_filter_specs'], $previous, $version);
            $this->configResource->patch(array(
                'input_filter_specs' => $newValues
            ), true);
        }

        // update zf-apigility
        if (isset($config['zf-apigility']['db-connected'])) {
            $newValues = $this->changeVersionArray($config['zf-apigility']['db-connected'], $previous, $version);
            $this->configResource->patch(array(
                'zf-apigility' => array('db-connected' => $newValues)
            ), true);
        }

        // update service_manager
        if (isset($config['service_manager'])) {
            $newValues = $this->changeVersionArray($config['service_manager'], $previous, $version);
            $this->configResource->patch(array(
                'service_manager' => $newValues
            ), true);
        }

        // update controllers
        if (isset($config['controllers'])) {
            $newValues = $this->changeVersionArray($config['controllers'], $previous, $version);
            $this->configResource->patch(array(
                'controllers' => $newValues
            ), true);
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
        $result = array();
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
        return str_replace('.', '\\', $module);
    }

    /**
     * Determine the source path for the module
     *
     * Usually, this is the "src/{modulename}" subdirectory of the
     * module.
     *
     * @param  string $module
     * @return string
     */
    protected function getModuleSourcePath($module)
    {
        $moduleClass = sprintf('%s\\Module', $module);

        if (!class_exists($moduleClass)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module %s doesn\'t exist',
                $module
            ));
        }

        $r       = new ReflectionClass($moduleClass);
        $srcPath = dirname($r->getFileName());
        if (file_exists($srcPath . '/src') && is_dir($srcPath . '/src')) {
            $srcPath = sprintf('%s/src/%s', $srcPath, str_replace('\\', '/', $moduleClass));
        }

        if (!file_exists($srcPath) && !is_dir($srcPath)) {
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
     * @param  string $module
     * @param  integer $previous Previous version
     * @param  integer $version New version
     * @return true
     */
    protected function updateDocumentationVersion($module, $previous, $version)
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
}
