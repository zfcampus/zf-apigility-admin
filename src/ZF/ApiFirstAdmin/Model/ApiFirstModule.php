<?php

namespace ZF\ApiFirstAdmin\Model;

use Zend\ModuleManager\ModuleManager;
use ZF\ApiFirst\ApiFirstModuleInterface;
use Zend\Code\Generator\ValueGenerator;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;

class ApiFirstModule
{
    /**
     * Endpoints for each module
     * @var array
     */
    protected $endpoints = array();

    /**
     * @var ModuleManager
     */
    protected $moduleManager;

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
     * @param  ModuleManager $moduleManager
     * @param  array $restConfig
     * @param  array $rpcConfig
     */
    public function __construct(ModuleManager $moduleManager, array $restConfig, array $rpcConfig)
    {
        $this->moduleManager = $moduleManager;
        $this->restConfig    = array_keys($restConfig);
        $this->rpcConfig     = array_keys($rpcConfig);
    }

    /**
     * Retrieve modules
     *
     * @return ModuleMetadata[]
     */
    public function getModules()
    {
        $modules = $this->getEnabledModules();
        return array_values($modules);
    }

    /**
     * @param  string $moduleName
     * @return null|ModuleMetadata
     */
    public function getModule($moduleName)
    {
        $moduleName = $this->normalizeModuleName($moduleName);
        $modules = $this->getEnabledModules();
        if (!array_key_exists($moduleName, $modules)) {
            return null;
        }
        return $modules[$moduleName];
    }

    /**
     * Returns list of all API-First-enabled modules
     *
     * @return array
     */
    protected function getEnabledModules()
    {
        if (is_array($this->modules)) {
            return $this->modules;
        }

        $this->modules = array();
        foreach ($this->moduleManager->getLoadedModules() as $moduleName => $module) {
            if (!$module instanceof ApiFirstModuleInterface) {
                continue;
            }

            $endpoints = $this->getEndpointsByModule($moduleName);
            $metadata  = new ModuleMetadata($moduleName, $endpoints['rest'], $endpoints['rpc']);
            $this->modules[$metadata->getName()] = $metadata;
        }

        return $this->modules;
    }

    /**
     * Retrieve all endpoints for a given module
     *
     * Returns null if the module is not API-enabled.
     *
     * Returns an array with the elements "rest" and "rpc" on success, with
     * each being an array of controller service names.
     *
     * @param  string $module
     * @return null|array
     */
    protected function getEndpointsByModule($module)
    {
        $endpoints = array(
            'rest' => $this->discoverEndpointsByModule($module, $this->restConfig),
            'rpc'  => $this->discoverEndpointsByModule($module, $this->rpcConfig),
        );
        return $endpoints;
    }

    /**
     * Loops through an array of controllers, determining which match the given module.
     *
     * @param  string $module
     * @param  array $config
     * @return array
     */
    protected function discoverEndpointsByModule($module, array $config)
    {
        $endpoints = array();
        foreach ($config as $controller) {
            if (strpos($controller, $module) === 0) {
                $endpoints[] = $controller;
            }
        }
        return $endpoints;
    }

    /**
     * Create a module
     *
     * @param string $module
     * @param string $path
     * @return boolen
     */
    public function createModule($module, $path = '.')
    {
        $modulePath = sprintf('%s/module/%s', $path, $module);
        if (file_exists($modulePath)) {
            return false;
        }

        mkdir("$modulePath/config", 0777, true);
        mkdir("$modulePath/src/$module", 0777, true);
        mkdir("$modulePath/view");

        if (!file_put_contents("$modulePath/config/module.config.php", "<" . "?php\nreturn array(\n);")) {
            return false;
        }
        
        $view = new ViewModel(array(
            'module' => $module
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
            'module/skeleton' => __DIR__ . '/../../../../view/module/skeleton.phtml'
        ));

        $view->setTemplate('module/skeleton');
        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if (!file_put_contents("$modulePath/Module.php", "<" . "?php\nrequire __DIR__ . '/src/$module/Module.php';")) { 
            return false;
        }
        if (!file_put_contents("$modulePath/src/$module/Module.php", "<" . "?php\n" . $renderer->render($view))) {
            return false;
        }

        // Add the module in application.config.php
        $application = require "$path/config/application.config.php";
        if (isset($application['modules']) && !in_array($module, $application['modules'])) {
            $application['modules'][] = $module;
            copy ("$path/config/application.config.php", "$path/config/application.config.old");
            $content = <<<EOD
<?php
/**
 * Configuration file generated by ZF API First Admin
 *
 * The previous config file has been stored in application.config.old
 */

EOD;

            $content .= 'return '. self::exportConfig($application) . ";\n";
            if (!file_put_contents("$path/config/application.config.php", $content)) {
                return false;
            }
        }
        return true;
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
     * @param  string $name 
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('\\', '.', $name);
    }
}
