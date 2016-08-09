<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ReflectionClass;
use Zend\EventManager\EventManager;
use ZF\Apigility\Admin\Exception;

class RestServiceModelFactory extends RpcServiceModelFactory
{
    const TYPE_DEFAULT      = RestServiceModel::class;
    const TYPE_DB_CONNECTED = DbConnectedRestServiceModel::class;

    /**
     * @param string $module
     * @param string $type
     * @return RestServiceModel
     * @throws Exception\InvalidArgumentException
     */
    public function factory($module, $type = self::TYPE_DEFAULT)
    {
        if (isset($this->models[$type][$module])) {
            return $this->models[$type][$module];
        }

        $moduleName   = $this->modules->normalizeModuleName($module);
        $config       = $this->configFactory->factory($module);
        $moduleEntity = $this->moduleModel->getModule($moduleName);

        $restModel = new RestServiceModel($moduleEntity, $this->modules, $config);
        $restModel->setEventManager($this->createEventManager());

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

    /**
     * Create and return an EventManager composing the shared event manager instance.
     *
     * @return EventManager
     */
    private function createEventManager()
    {
        $r = new ReflectionClass(EventManager::class);

        if ($r->hasMethod('setSharedManager')) {
            // zend-eventmanager v2 initialization
            $eventManager = new EventManager();
            $eventManager->setSharedManager($this->sharedEventManager);
            return $eventManager;
        }

        // zend-eventmanager v3 initialization
        return new EventManager($this->sharedEventManager);
    }
}
