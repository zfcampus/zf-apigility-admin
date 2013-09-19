<?php

namespace ZF\ApigilityAdmin\Model;

use ZF\ApigilityAdmin\Exception;

class RestServiceModelFactory extends RpcServiceModelFactory
{
    const TYPE_DEFAULT      = 'ZF\ApigilityAdmin\Model\RestServiceModel';
    const TYPE_DB_CONNECTED = 'ZF\ApigilityAdmin\Model\DbConnectedRestServiceModel';

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

        $config    = $this->configFactory->factory($module);
        $restModel = new RestServiceModel($this->normalizeModuleName($module), $this->modules, $config);
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
