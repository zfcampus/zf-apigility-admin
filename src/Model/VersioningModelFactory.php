<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ResourceFactory as ConfigResourceFactory;

/**
 * Class VersioningModelFactory
 *
 * @deprecated since 1.5; use \ZF\Apigility\Admin\Model\ModuleVersioningModelFactory instead
 */
class VersioningModelFactory implements ModuleVersioningModelFactoryInterface
{
    /**
     * @var ConfigResourceFactory
     */
    protected $configFactory;

    /**
     * Already created model instances
     *
     * @var array
     */
    protected $models = [];

    /**
     * @var ModulePathSpec
     */
    protected $moduleUtils;

    /**
     * @param ConfigResourceFactory $configFactory
     * @param ModulePathSpec $moduleUtils
     * @deprecated
     */
    public function __construct(ConfigResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * @param  string $module
     * @return VersioningModel
     * @deprecated
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $moduleName = $this->moduleUtils->normalizeModuleName($module);
        $config     = $this->configFactory->factory($moduleName);
        $docsConfig = $this->getDocsConfig($module);

        $this->models[$module] = new VersioningModel(
            $config,
            $docsConfig,
            $this->moduleUtils
        );

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     * @deprecated
     */
    protected function normalizeModuleName($name)
    {
        return $this->moduleUtils->normalizeModuleName($name);
    }

    /**
     * getDocsConfig
     * @param $module
     * @return null|\ZF\Configuration\ConfigResource
     * @deprecated
     */
    protected function getDocsConfig($module)
    {
        $moduleConfigPath = $this->moduleUtils->getModuleConfigPath($module);
        $docConfigPath    = dirname($moduleConfigPath) . '/documentation.config.php';
        if (! file_exists($docConfigPath)) {
            return null;
        }
        $documentation = include $docConfigPath;
        return $this->configFactory->createConfigResource($documentation, $docConfigPath);
    }
}
