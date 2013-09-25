<?php
namespace ZF\Apigility\Admin\Model;

use ZF\Apigility\Admin\Exception;

class VersioningModel
{
    /**
     * Create a new version for a module
     *
     * @param  string $module
     * @param  integer $ver
     * @param  string $path
     * @return boolen
     */
    public function createVersion($module, $ver, $path = '.')
    {
        $modulePath = sprintf("%s/module/%s", $path, $module);
        if (!file_exists($modulePath)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The module %s doesn\'t exist', $module
            ));
        }

        $versions = $this->getModuleVersions($module, $path);
        if (in_array($ver, $versions)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The API version %d of the module %s already exists', $ver, $module
            ));
        }

        $prev = (int) $ver - 1;
        if (!in_array($prev, $versions)) {
            throw new Exception\RuntimeException(sprintf(
                'The previous API version %d doesn\'t exist, I cannot create version %d', $prev, $ver
            ));
        }

        $srcPath = sprintf("%s/src/%s", $modulePath, $module);
        $this->recursiveCopy($srcPath . '/V'. $prev, $srcPath . '/V' . $ver);

        foreach (glob($modulePath . '/config/*.config.php') as $file) {
            $this->updateConfigVersion($file, $prev, $ver);
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
        foreach (glob($srcPath . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match('/\\V(\d+)$/', $dir, $match)) {
                $versions[] = (int) $match[1];
            }
        }
        return $versions;
    }

    /** 
     * Copy file and folder recursively
     *
     * @param string $src
     * @param string $dst
     */
    protected function recursiveCopy($src, $dst) 
    { 
        $dir = opendir($src);
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file); 
                } else { 
                    copy($src . '/' . $file, $dst . '/' . $file); 
                } 
            } 
        } 
        closedir($dir); 
    }


    /**
     * Update a PHP configuration file from $prev to $ver version
     *
     * @param string $file
     * @param integer $prev
     * @param integer $ver
     * @return boolean
     */
    protected function updateConfigVersion($file, $prev, $ver)
    {
        $config = include($file);
        if (empty($config)) {
            return false;
        }
        $prev = (int) $ver - 1;
        
        // update zf-hal.metadata_map
        if (isset($config['zf-hal']['metadata_map'])) {
            $newValues = $this->changeVersionArray($config['zf-hal']['metadata_map'], $prev, $ver);
            $config['zf-hal']['metadata_map'] = array_merge($config['zf-hal']['metadata_map'], $newValues);
        }
        
        // @todo update zf-rpc

        // @todo update zf-rest
        
        // @todo update zf-content-negotiation
        
        copy($file, $file . '.V' . $prev . '.old');
        return (false !== file_put_contents($file, '<?php return ' . var_export($config, true)));
    }
    

    /**
     * Change version in a string
     *
     * @param  string $string
     * @param  integer $prev
     * @param  integer $prev
     * @return string
     */
    protected function changeVersionString($string, $prev, $ver)
    {
        return str_replace('\\V' . $prev . '\\', '\\V' . $ver . '\\', $string);
    }

    /**
     * Change version in an array
     *
     * @param  array $data
     * @param  integer $prev
     * @param  integer $ver
     * @return array
     */
    protected function changeVersionArray($data, $prev, $ver)
    {
        $result = array();
        foreach ($data as $key => $value) {
            $newKey = $this->changeVersionString($key, $prev, $ver); 
            if (is_array($value)) {
                $result[$newKey] = $this->changeVersionArray($value, $prev, $ver);
            } else {
                $result[$newKey] = $this->changeVersionString($value, $prev, $ver);
            }
        }
        return $result; 
    }
    
}
