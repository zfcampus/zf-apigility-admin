<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Apigility\Admin\Exception;

class RestServiceModelFactory extends RpcServiceModelFactory
{
    const TYPE_DEFAULT      = 'ZF\Apigility\Admin\Model\RestServiceModel';
    const TYPE_DB_CONNECTED = 'ZF\Apigility\Admin\Model\DbConnectedRestServiceModel';

    /**
     * @param  string $module
     * @return RestServiceModel
     */
    public function factory($module, $type = self::TYPE_DEFAULT)
    {
        if (isset($this->models[$type])
            && isset($this->models[$type][$module])
        ) {
            return $this->models[$type][$module];
        }

        $moduleName   = $this->normalizeModuleName($module);
        $config       = $this->configFactory->factory($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);

        $restModel = new RestServiceModel($moduleEntity, $this->modules, $config);
        $restModel->getEventManager()->setSharedManager($this->sharedEventManager);

        switch ($type) {
            case self::TYPE_DEFAULT:
                $this->models[$type][$module] = $restModel;
                return $restModel;
            case self::TYPE_DB_CONNECTED:
                $model = new $type($restModel);
                $this->models[$type][$module] = $model;
                return $model;
            default:
                throw new Exception\InvalidArgumentException(sprintf(
                    'Model of type "%s" does not exist or cannot be handled by this factory',
                    $type
                ));
        }
    }
}
