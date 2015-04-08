<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ResourceFactory as ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;

class AuthorizationModelFactory
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
    protected $models = array();

    /**
     * @var ModuleModel
     */
    protected $moduleModel;

    /**
     * @var ModuleUtils
     */
    protected $modules;

    /**
     * @param  ModuleUtils $modules
     * @param  ConfigResourceFactory $configFactory
     * @param  ModuleModel $moduleModel
     */
    public function __construct(ModulePathSpec $modules, ConfigResourceFactory $configFactory, ModuleModel $moduleModel)
    {
        $this->modules            = $modules;
        $this->configFactory      = $configFactory;
        $this->moduleModel        = $moduleModel;
    }

    /**
     * @param  string $module
     * @return AuthorizationModel
     */
    public function factory($module)
    {
        if (isset($this->models[$module])) {
            return $this->models[$module];
        }

        $moduleName   = $this->normalizeModuleName($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);
        $config       = $this->configFactory->factory($module);

        $this->models[$module] = new AuthorizationModel($moduleEntity, $this->modules, $config);

        return $this->models[$module];
    }

    /**
     * @param  string $name
     * @return string
     */
    protected function normalizeModuleName($name)
    {
        return str_replace('.', '\\', $name);
    }
}
