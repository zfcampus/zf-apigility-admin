<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

class HydratorsModelFactory extends AbstractPluginManagerModelFactory
{
    /**
     * @var string
     */
    protected $pluginManagerService = 'HydratorManager';

    /**
     * @var string
     */
    protected $pluginManagerModel = 'ZF\Apigility\Admin\Model\HydratorsModel';
}
