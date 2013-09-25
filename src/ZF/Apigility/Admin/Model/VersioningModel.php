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
     * Update the version of the PHP source code
     *
     * @param  string $dir
     * @param  integer $ver
     * @return boolean
     */
    protected function updateVersionSourceCode($dir, $ver) 
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->updateVersionSourceCode($file, $ver);
            }
            $content = file_get_contents($file);
            if (false !== $content) {
                // Update version on namespace and Windows path
                $content = str_replace('\\V' . ($ver - 1), '\\V' . $ver, $content);
                // Update version on Unix path
                $content = str_replace('/V' . ($ver - 1), '/V' . $ver, $content);
                // Update file
                file_put_contents($file, $content);
            }
        }
    }
}
