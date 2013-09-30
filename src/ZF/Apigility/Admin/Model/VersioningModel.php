<?php
namespace ZF\Apigility\Admin\Model;

use Zend\Stdlib\Glob;
use ZF\Apigility\Admin\Exception;
use ZF\Configuration\ConfigResource;

class VersioningModel
{
    protected $configResource;

    /**
     * @param  ConfigResource $config
     */
    public function __construct(ConfigResource $config)
    {
        $this->configResource = $config;
    }

    /**
     * Create a new version for a module
     *
     * @param  string $module
     * @param  integer $version
     * @param  string $path
     * @return boolean
     */
    public function createVersion($module, $version, $path = '.')
    {
        $modulePath = sprintf("%s/module/%s", $path, $module);
        if (!file_exists($modulePath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module %s doesn\'t exist',
                $module
            ));
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

        $srcPath = sprintf("%s/src/%s", $modulePath, $module);
        $this->recursiveCopy($srcPath . '/V'. $previous, $srcPath . '/V' . $version, $previous, $version);

        foreach (Glob::glob($modulePath . '/config/*.config.php') as $file) {
            $this->updateConfigVersion($module, $file, $previous, $version);
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
    public function getModuleVersions($module, $path = '.')
    {
        $srcPath = sprintf('%s/module/%s/src/%s', $path, $module, $module);
        if (!file_exists($srcPath)) {
            return false;
        }

        $versions  = array();
        foreach (Glob::glob($srcPath . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match('/\\V(?P<version>\d+)$/', $dir, $matches)) {
                $versions[] = (int) $matches['version'];
            }
        }
        return $versions;
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
        while(false !== ( $file = readdir($dir)) ) {
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
     * @param  integer $previous
     * @param  integer $version
     * @return boolean
     */
    protected function updateConfigVersion($module, $file, $previous, $version)
    {
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
            foreach (array('controllers', 'accept-whitelist', 'content-type-whitelist') as $key) {
                if (isset($config['zf-content-negotiation'][$key])) {
                    $newValues = $this->changeVersionArray($config['zf-content-negotiation'][$key], $previous, $version);
                    // change version in mediatype
                    if (in_array($key, array('accept-whitelist', 'content-type-whitelist'))) {
                        foreach ($newValues as $k => $v){
                            $newValues[$k] = array(
                                'application/' . strtolower($module) . '.v' . $version . '+json'
                            );
                        }
                    }

                    $this->configResource->patch(array(
                        'zf-content-negotiation' => array($key => $newValues)
                    ), true);
                }
            }
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
}
