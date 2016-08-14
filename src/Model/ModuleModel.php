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
     * @var bool
     */
    protected static $useShortArrayNotation = false;

    /**
     * @var ValueGenerator
     */
    protected static $valueGenerator;

    /**
     * Services for each module
     * @var array
     */
    protected $services = [];

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
     * @param  int $indent the initial indentation value
     * @return string
     */
    public static function exportConfig($config, $indent = 0)
    {
        if (empty(static::$valueGenerator)) {
            static::$valueGenerator = new ValueGenerator();
        }
        static::$valueGenerator->setValue($config);
        static::$valueGenerator->setType(
            static::$useShortArrayNotation
            ? ValueGenerator::TYPE_ARRAY_SHORT
            : ValueGenerator::TYPE_ARRAY_LONG
        );
        static::$valueGenerator->setArrayDepth($indent);

        return static::$valueGenerator;
    }

    /**
     * Set the flag indicating whether or not generated config files should use
     * short array notation.
     *
     * @var bool $flag
     * @return void
     */
    public function setUseShortArrayNotation($flag = true)
    {
        static::$useShortArrayNotation = (bool) $flag;
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
        $modules = $this->getEnabledModules();
        if (! array_key_exists($moduleName, $modules)) {
            return null;
        }

        return $modules[$moduleName];
    }

    /**
     * Create a module
     *
     * @param  string $module
     * @param  ModulePathSpec $pathSpec
     * @return bool
     * @throws \Exception
     */
    public function createModule($module, ModulePathSpec $pathSpec)
    {
        $path = $pathSpec->getApplicationPath();
        $application = require sprintf('%s/config/application.config.php', $path);
        if (is_array($application)
            && isset($application['modules'])
            && in_array($module, $application['modules'], true)
        ) {
            // Module already exists in configuration
            return false;
        }

        $modulePath = $pathSpec->getModulePath($module);
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

        $payload = static::$useShortArrayNotation
            ? "[\n]"
            : "array(\n)";
        $payload = sprintf('<' . "?php\n return %s;", $payload);
        if (! file_put_contents(sprintf('%s/module.config.php', $moduleConfigPath), $payload)) {
            return false;
        }

        $view = new ViewModel([
            'module' => $module,
        ]);

        $resolver = new Resolver\TemplateMapResolver([
            'module/skeleton'      => __DIR__ . '/../../view/module/skeleton.phtml',
            'module/skeleton-psr4' => __DIR__ . '/../../view/module/skeleton-psr4.phtml',
        ]);

        $renderer = new PhpRenderer();
        $renderer->setResolver($resolver);

        if ($pathSpec->getPathSpec() === ModulePathSpec::PSR_0) {
            $view->setTemplate('module/skeleton');
            $moduleRelClassPath = sprintf('%s/Module.php', $moduleSourceRelativePath);

            if (! file_put_contents(
                sprintf('%s/Module.php', $modulePath),
                "<" . "?php\nrequire __DIR__ . '$moduleRelClassPath';"
            )) {
                return false;
            }
            if (! file_put_contents(
                sprintf('%s/Module.php', $moduleSourcePath),
                "<" . "?php\n" . $renderer->render($view)
            )) {
                return false;
            }
        } else {
            $view->setTemplate('module/skeleton-psr4');
            if (! file_put_contents(sprintf('%s/Module.php', $modulePath), "<" . "?php\n" . $renderer->render($view))) {
                return false;
            }
        }

        // Add the module in application.config.php
        if (is_array($application)
            && isset($application['modules'])
            && ! in_array($module, $application['modules'], true)
        ) {
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
     * @return bool
     */
    public function updateModule($module)
    {
        $modules = $this->moduleManager->getLoadedModules();

        if (! isset($modules[$module])) {
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
        if (! file_put_contents($objModule->getFileName(), $replacement)) {
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
     * @return bool
     */
    public function deleteModule($module, $path = '.', $recursive = false)
    {
        $application = require sprintf('%s/config/application.config.php', $path);
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

        $this->modules = [];
        foreach ($this->moduleManager->getLoadedModules() as $moduleName => $module) {
            if (! $module instanceof ApigilityProviderInterface && ! $module instanceof ApigilityModuleInterface) {
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
            $entity->exchangeArray([
                'versions'        => $versions,
                'default_version' => $this->getModuleDefaultVersion($module),
            ]);

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
        if (! $module instanceof ApigilityProviderInterface && ! $module instanceof ApigilityModuleInterface) {
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
        $services = [
            'rest' => $this->discoverServicesByModule($module, $this->restConfig),
            'rpc'  => $this->discoverServicesByModule($module, $this->rpcConfig),
        ];
        return $services;
    }

    /**
     * Retrieve versions by module
     *
     * Checks each REST and RPC service name for a
     * version subnamespace; if found, that version
     * is added to the list.
     *
     * @param string $moduleName
     * @param ApigilityProviderInterface|ApigilityModuleInterface $module
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    protected function getVersionsByModule($moduleName, $module)
    {
        if (! $module instanceof ApigilityProviderInterface
            && ! $module instanceof ApigilityModuleInterface
        ) {
            throw new Exception\InvalidArgumentException(
                'Expected ApigilityProviderInterface or ApigilityModuleInterface'
            );
        }

        $path = $this->detectSourcePathFromModule($moduleName, $module);

        $versions = [];
        foreach (Glob::glob($path . DIRECTORY_SEPARATOR . 'V*') as $dir) {
            if (preg_match('/\\V(?P<version>\d+)$/', $dir, $matches)) {
                $versions[] = (int) $matches['version'];
            }
        }

        if (! $versions) {
            return [1];
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
        $services = [];
        foreach ($config as $controller) {
            if (strpos($controller, $module) === 0) {
                $services[] = $controller;
            }
        }
        return $services;
    }

    /**
     * Write application configuration.
     *
     * If a "modules.config.php" exists, writes to that with the "modules"
     * subkey of the provided configuration; otherwise, writes to
     * application.config.php.
     *
     * @param  array $application Application configuration.
     * @param  string $path Base path of the application.
     * @return bool Whether or not the operation was successful.
     */
    protected function writeApplicationConfig(array $application, $path)
    {
        $modulesConfigFile = sprintf('%s/config/modules.config.php', $path);
        if (file_exists($modulesConfigFile)) {
            return $this->writeConfigFile($application['modules'], $modulesConfigFile);
        }

        return $this->writeConfigFile($application, sprintf('%s/config/application.config.php', $path));
    }

    /**
     * Write a configuration file.
     *
     * Writes a configuration file, after first creating an archived version of
     * it with the suffix '.old'.
     *
     * @param array $config Configuration to export.
     * @param string $configFile Configuration file to write.
     * @return bool
     */
    protected function writeConfigFile(array $config, $configFile)
    {
        $archiveFile = preg_replace('/\.php$/', '.old', $configFile);
        copy($configFile, $archiveFile);
        $content = <<<EOD
<?php
/**
 * Configuration file generated by ZF Apigility Admin
 *
 * The previous config file has been stored in $archiveFile
 */

EOD;

        $content .= 'return '. self::exportConfig($config) . ";\n";
        if (! file_put_contents($configFile, $content)) {
            return false;
        }

        return true;
    }

    /**
     * Determine where the source path is for a module.
     *
     * The path will vary based on:
     *
     * - PSR-0 module generated by Apigility
     * - PSR-4 module generated by Apigility, using ModuleAutoloader
     * - PSR-4 module using Composer autoloading, with Module class under the source tree
     *
     * The first case has been covered since the beginning, and the third has
     * "just worked", but the second causes problems due to the fact that the
     * Module class is in the root of the module (PSR-0 solved this by having that file
     * require the class file within the source tree).
     *
     * @param string $moduleName
     * @param ApigilityProviderInterface|ApigilityModuleInterface $module
     * @return string
     */
    private function detectSourcePathFromModule($moduleName, $module)
    {
        $r       = new ReflectionObject($module);
        $path    = dirname($r->getFileName());
        $ds      = sprintf('[/%s]', preg_quote('\\'));
        $pattern = sprintf('#%ssrc(%s%s)?$#', $ds, $ds, preg_quote($moduleName));

        if (! preg_match($pattern, $path)) {
            $path .= DIRECTORY_SEPARATOR . 'src';
        }

        return $path;
    }
}
