<?php

namespace ZF\Apigility\Admin\Model;

use Zend\ModuleManager\ModuleManager;
use ZF\Apigility\ApigilityModuleInterface;
use Zend\Code\Generator\ValueGenerator;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ReflectionClass;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Exception;

class VersioningModel
{
    /**
     * Services for each module
     * @var array
     */
    protected $services = array();

    /**
     * @var ModuleModel
     */
    protected $moduleModel;

    /**
     * @var array
     */
    protected $modules;

    /**
     * @var array
     */
    protected $restConfig;

    /**
     * @var array
     */
    protected $rpcConfig;

    /**
     * @var ValueGenerator
     */
    protected static $valueGenerator;

    /**
     * @param  ModuleModel $moduleModel
     */
    public function __construct(ModuleModel $moduleModel)
    {
        $this->moduleModel = $moduleModel;
    }

    /**
     * Export the $config array in a human readable format
     *
     * @param  array $config
     * @param  integer $space the initial indentation value
     * @return string
     */
    public static function exportConfig($config, $indent = 0)
    {
        if (empty(static::$valueGenerator)) {
            static::$valueGenerator = new ValueGenerator();
        }
        static::$valueGenerator->setValue($config);
        static::$valueGenerator->setArrayDepth($indent);

        return static::$valueGenerator;
    }

    /**
     * Create a new version for a module
     *
     * @param  string $module
     * @param  integer $ver
     * @param  boolean $copy
     * @return boolen
     */
    public function createVersion($module, $ver, $copy = true, $path = '.')
    {
        $name = $this->getNameWithoutVersion($module);
        if ($this->moduleModel->getModule($name . '\\V' . $ver)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The version %d of the module %s already exists', $ver, $module
            ));
        }

        if (!$copy) {
            return $this->moduleModel->createModule($name, $path, $ver);
        } 

        $previous = (int) $ver - 1;
        if (!$this->moduleModel->getModule($name . '\\V' . $previous)) {
            throw new Exception\RuntimeException(sprintf(
                'The module %s\\V%d doesn\'t exist, I cannot create the version %d',
                $name,
                $previous,
                $ver
            ));
        }

        $class = new ReflectionClass($name . '\\V' . $previous . '\\Module');
        $moduleFile = $class->getFileName();
        
        // get the path without version
        $pathModule = strstr($moduleFile, DIRECTORY_SEPARATOR . 'V' . $previous, true);

        $this->recursiveCopyAndUpdate($pathModule . '/V'. $previous, $pathModule . '/V' . $ver, $ver);
        return true;
    }

    /**
     * Get the version of a module
     *
     * @param  string $module
     * @return integer|boolean
     */ 
    protected function getVersion($module)
    {
        if (preg_match('/\\V(\d+)$/', $module, $match)) {
            return (int) $match[1];
        }
        return false;
    }

    /**
     * Get the name of a module without the version
     *
     * @param  string $module
     * @return string|boolean
     */
    protected function getNameWithoutVersion($module)
    {
        if (preg_match('/^(.+)\\\\V\d+$/', $module, $match)) {
            return $match[1];
        }
        return false;
    }

    /** 
     * Copy file and folder recursively
     *
     * @param string $src
     * @param string $dst
     */
    protected function recursiveCopyAndUpdate($src, $dst, $ver) 
    { 
        $dir = opendir($src);
        $dst = str_replace(DIRECTORY_SEPARATOR . 'V' . ($ver - 1), DIRECTORY_SEPARATOR . 'V' . $ver, $dst); 
        @mkdir($dst); 
        while(false !== ( $file = readdir($dir)) ) { 
            if (( $file != '.' ) && ( $file != '..' )) { 
                if ( is_dir($src . '/' . $file) ) { 
                    $this->recursiveCopyAndUpdate($src . '/' . $file, $dst . '/' . $file, $ver); 
                } else { 
                    copy($src . '/' . $file, $dst . '/' . $file); 
                    $this->updateVersionSourceCode($dst . '/' . $file, $ver);
                } 
            } 
        } 
        closedir($dir); 
    }

    /**
     * Update the version of the PHP source code
     *
     * @param string $file
     * @param integer $ver
     * @return boolean
     */
    protected function updateVersionSourceCode($file, $ver) 
    {
        $content = file_get_contents($file);
        if (false === $content) {
            return false;
        }
        
        // Update version on namespace and Windows path
        $content = str_replace('\\V' . ($ver - 1), '\\V' . $ver, $content);
        // Update version on Unix path
        $content = str_replace('/V' . ($ver -1), '/V' . $ver, $content);

        return (file_put_contents($file, $content) > 0);
    }
}
