<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ReflectionObject;
use Zend\Code\Generator\ValueGenerator;
use Zend\ModuleManager\ModuleManager;
use Zend\Stdlib\Glob;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver;
use ZF\Apigility\Admin\Exception;
use ZF\Apigility\Admin\Utility;
use ZF\Apigility\ApigilityModuleInterface;
use ZF\Apigility\Provider\ApigilityProviderInterface;

class ModuleModel
{
    /**
     * Services for each module
     * @var array
     */
    protected $services = array();

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

    protected $modulePathSpec;

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
     * Retrieve modules
     *
     * @return ModuleEntity[]
     */
    public function getModules()
    {
        $modules = $this->getEnabledModules();
        return array_values($modules);
    }

    /**
     * @param  string $moduleName
     * @return null|ModuleEntity
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
     * Create a module
     *
     * @param  string $module
     * @param  string $path
     * @param  integer $ver
     * @return boolean
     */
    public function createModule($module, ModulePathSpec $pathSpec)
    {
        $path = $pathSpec->getApplicationPath();
        $application = require "$path/config/application.config.php";
        if (is_array($application)
            && isset($application['modules'])
            && in_array($module, $application['modules'], true)
        ) {
            // Module already exists in configuration
            return false;
        }

        $modulePath = $pathSpec->getModulePath($module, $path);
        if (file_exists($modulePath)) {
            throw new \Exception(sprintf(
                'Cannot create new API module; module by the name "%s" already exists',
                $module
            ), 409);
        }

        $moduleSourcePath         = $pathSpec->getModuleSourcePath($module);
        $moduleSourceRelativePath = $pathSpec->getModuleSourcePath($module, false);
        $moduleConfigPath         = $pathSpec->getModuleConfigPath($module);

        mkdir($moduleConfigPath, 0775, true);
        mkdir($pathSpec->getModuleViewPath($module), 0775, true);
        mkdir($pathSpec->getRestPath($module, 1), 0775, true);
        mkdir($pathSpec->getRpcPath($module, 1), 0775, true);

        if (!file_put_contents("$moduleConfigPath/module.config.php", "<" . "?php\nreturn array(\n);")) {
            return false;
        }

        $view = new ViewModel(array(
            'module'  => $module
        ));

        $resolver = new Resolver\TemplateMapResolver(array(
            'module/skeleton' => __DIR__ . '/../../view/module/skeleton.phtml',
            'module/skeleton-psr4' => __DIR__ . '/../../view/module/skeleton-psr4.phtml',
        ));

        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if ($pathSpec->getPathSpec() === 'psr-0') {
            $view->setTemplate('module/skeleton');
            $moduleRelClassPath = "$moduleSourceRelativePath/Module.php";

            if (!file_put_contents("$modulePath/Module.php", "<" . "?php\nrequire __DIR__ . '$moduleRelClassPath';")) {
                return false;
            }
            if (!file_put_contents("$moduleSourcePath/Module.php", "<" . "?php\n" . $renderer->render($view))) {
                return false;
            }
        } else {
            $view->setTemplate('module/skeleton-psr4');
            if (!file_put_contents("$modulePath/Module.php", "<" . "?php\n" . $renderer->render($view))) {
                return false;
            }
        }

        // Add the module in application.config.php
        if (isset($application['modules']) && !in_array($module, $application['modules'], true)) {
            $application['modules'][] = $module;
            if (! $this->writeApplicationConfig($application, $path)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Update a module (adding the ApigilityModule interface)
     *
     * @param  string $module
     * @return boolean
     */
    public function updateModule($module)
    {
        $modules = $this->moduleManager->getLoadedModules();

        if (!isset($modules[$module])) {
            return false;
        }

        if ($modules[$module] instanceof ApigilityModuleInterface) {
            return false;
        }

        if ($modules[$module] instanceof ApigilityProviderInterface) {
            return false;
        }

        $objModule = new ReflectionObject($modules[$module]);
        $content   = file_get_contents($objModule->getFileName());

        $replacement = preg_replace(
            '/' . "\n" . 'class\s([a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*)\s{/i',
            "\nuse ZF\Apigility\Provider\ApigilityProviderInterface;\n\n'
            . 'class $1 implements ApigilityProviderInterface\n{",
            $content
        );

        if ($replacement === $content) {
            $replacement = preg_replace(
                '/implements\s/',
                'implements ZF\Apigility\Provider\ApigilityProviderInterface,',
                $content
            );
        }

        copy($objModule->getFileName(), $objModule->getFileName() . '.old');
        if (!file_put_contents($objModule->getFileName(), $replacement)) {
            return false;
        }

        return true;
    }

    /**
     * Delete an existing module
     *
     * @param  string $module
     * @param  string $path
     * @param  bool $recursive
     * @return boolean
     */
    public function deleteModule($module, $path = '.', $recursive = false)
    {
        $application = require "$path/config/application.config.php";
        if (! is_array($application)
            || ! isset($application['modules'])
            || ! in_array($module, $application['modules'], true)
        ) {
            // Module does not exist in configuration; nothing to do
            return true;
        }

        $modules = array_filter($application['modules'], function ($value) use ($module) {
            return ($module !== $value);
        });
        $application['modules'] = $modules;
        if (! $this->writeApplicationConfig($application, $path)) {
            // error writing application config
            return false;
        }

        if (! $recursive) {
            // Not a recursive deletion? done
            return true;
        }

        $modulePath = sprintf('%s/module/%s', $path, $module);
        if (! file_exists($modulePath)) {
            // module path does not exist; we can be done.
            return true;
        }

        Utility::recursiveDelete($modulePath);
        return true;
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
            if (!$module instanceof ApigilityProviderInterface && !$module instanceof ApigilityModuleInterface) {
                continue;
            }

            if ($module instanceof ApigilityModuleInterface) {
                trigger_error(
                    'ZF\Apigility\ApigilityModuleInterface is deprecated,
                    use ZF\Apigility\Provider\ApigilityProviderInterface instead',
                    E_USER_DEPRECATED
                );
            }

            $services = $this->getServicesByModule($moduleName);
            $versions = $this->getVersionsByModule($moduleName, $module);
            $entity   = new ModuleEntity($moduleName, $services['rest'], $services['rpc']);
            $entity->exchangeArray(array(
                'versions'        => $versions,
                'default_version' => $this->getModuleDefaultVersion($module),
            ));

            $this->modules[$entity->getName()] = $entity;
        }

        return $this->modules;
    }

    /**
     * Retrieves the configured default version for the specified module.
     *
     * @param  ApigilityModuleInterface|ApigilityProviderInterface $module
     * @throws \ZF\Apigility\Admin\Exception\InvalidArgumentException
     * @return int
     */
    protected function getModuleDefaultVersion($module)
    {
        if (!$module instanceof ApigilityProviderInterface && !$module instanceof ApigilityModuleInterface) {
            throw new Exception\InvalidArgumentException(
                'Expected ApigilityProviderInterface or ApigilityModuleInterface'
            );
        }
        if (! method_exists($module, 'getConfig')) {
            return 1;
        }

        $config = $module->getConfig();
        return isset($config['zf-versioning']['default_version']) ? $config['zf-versioning']['default_version'] : 1;
    }

    /**
     * Retrieve all services for a given module
     *
     * Returns null if the module is not API-enabled.
     *
     * Returns an array with the elements "rest" and "rpc" on success, with
     * each being an array of controller service names.
     *
     * @param  string $module
     * @return null|array
     */
    protected function getServicesByModule($module)
    {
        $services = array(
            'rest' => $this->discoverServicesByModule($module, $this->restConfig),
            'rpc'  => $this->discoverServicesByModule($module, $this->rpcConfig),
        );
        return $services;
    }

    /**
     * Retrieve versions by module
     *
     * Checks each REST and RPC service name for a
     * version subnamespace; if found, that version
     * is added to the list.
     *
     * @param  string $moduleName
     * @param $module
     * @throws \ZF\Apigility\Admin\Exception\InvalidArgumentException
     * @internal param array $services
     * @return array
     */
    protected function getVersionsByModule($moduleName, $module)
    {
        if (!$module instanceof ApigilityProviderInterface && !$module instanceof ApigilityModuleInterface) {
            throw new Exception\InvalidArgumentException(
                'Expected ApigilityProviderInterface or ApigilityModuleInterface'
            );
        }

        $r        = new ReflectionObject($module);
        $path     = dirname($r->getFileName());
        $dirSep   = sprintf('(?:%s|%s)', preg_quote('/'), preg_quote('\\'));
        $pattern = sprintf(
            '#%ssrc%s%s#',
            $dirSep,
            $dirSep,
            str_replace('\\', $dirSep, $moduleName)
        );
        if (!preg_match($pattern, $path)) {
            $path = sprintf('%s/src/%s', $path, str_replace('\\', '/', $moduleName));
        }
        if (!file_exists($path)) {
            return array(1);
        }

        $versions  = array();
        foreach (Glob::glob($path . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match('/\\V(?P<version>\d+)$/', $dir, $matches)) {
                $versions[] = (int) $matches['version'];
            }
        }

        if (empty($versions)) {
            return array(1);
        }

        sort($versions);
        return $versions;
    }

    /**
     * Loops through an array of controllers, determining which match the given module.
     *
     * @param  string $module
     * @param  array $config
     * @return array
     */
    protected function discoverServicesByModule($module, array $config)
    {
        $services = array();
        foreach ($config as $controller) {
            if (strpos($controller, $module) === 0) {
                $services[] = $controller;
            }
        }
        return $services;
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('\\', '.', $name);
    }

    /**
     * Write application configuration
     *
     * @param  array $application
     * @param  string $path
     * @return bool
     */
    protected function writeApplicationConfig(array $application, $path)
    {
        copy("$path/config/application.config.php", "$path/config/application.config.old");
        $content = <<<EOD
<?php
/**
 * Configuration file generated by ZF Apigility Admin
 *
 * The previous config file has been stored in application.config.old
 */

EOD;

        $content .= 'return '. self::exportConfig($application) . ";\n";
        if (!file_put_contents("$path/config/application.config.php", $content)) {
            return false;
        }

        return true;
    }
}
