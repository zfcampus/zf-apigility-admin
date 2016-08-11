<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ResourceFactory;

/**
 * Class ModuleVersioningModelFactory
 * @author Gabriel Somoza <gabriel@somoza.me>
 */
class ModuleVersioningModelFactory implements ModuleVersioningModelFactoryInterface
{
    /** @var ResourceFactory */
    private $configFactory;

    /** @var ModulePathSpec */
    private $moduleUtils;

    /** @var ModuleVersioningModel[] */
    private $models = [];

    /**
     * @param ResourceFactory $configFactory
     * @param ModulePathSpec $moduleUtils
     */
    public function __construct(ResourceFactory $configFactory, ModulePathSpec $moduleUtils)
    {
        $this->configFactory = $configFactory;
        $this->moduleUtils   = $moduleUtils;
    }

    /**
     * Create service
     *
     * @param string $module
     *
     * @return ModuleVersioningModel
     */
    public function factory($module)
    {
        $moduleName = $this->moduleUtils->normalizeModuleName($module);

        if (! isset($this->models[$moduleName])) {
            $config     = $this->configFactory->factory($moduleName);
            $docsConfig = $this->getDocsConfig($moduleName);

            $this->models[$moduleName] = ModuleVersioningModel::createWithPathSpec(
                $moduleName,
                $this->moduleUtils,
                $config,
                $docsConfig
            );
        }

        return $this->models[$moduleName];
    }

    /**
     * getDocsConfig
     * @param $module
     * @return null|\ZF\Configuration\ConfigResource
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
