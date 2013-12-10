<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Validator\ValidatorPluginManager;
use Zend\ServiceManager\ServiceManager;
use ZF\Apigility\Admin\Exception;

class ValidatorsModel extends AbstractPluginManagerModel
{
    /**
     * $pluginManager should be an instance of
     * Zend\Validator\ValidatorPluginManager.
     *
     * @param ServiceManager $pluginManager
     */
    public function __construct(ServiceManager $pluginManager)
    {
        if (! $pluginManager instanceof ValidatorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Zend\Validator\ValidatorPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }
        parent::__construct($pluginManager);
    }
}
