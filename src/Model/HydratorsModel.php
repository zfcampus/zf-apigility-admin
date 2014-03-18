<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZF\Apigility\Admin\Exception;

class HydratorsModel extends AbstractPluginManagerModel
{
    /**
     * $pluginManager should be an instance of
     * Zend\Stdlib\Hydrator\HydratorPluginManager.
     *
     * @param ServiceManager $pluginManager
     */
    public function __construct(ServiceManager $pluginManager)
    {
        if (! $pluginManager instanceof HydratorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Zend\Stdlib\Hydrator\HydratorPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }
        parent::__construct($pluginManager);
    }
}
