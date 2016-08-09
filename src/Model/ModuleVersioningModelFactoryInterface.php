<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

/**
 * Used primarily to provide backwards-compatibility
 *
 * @author Gabriel Somoza <gabriel@somoza.me>
 */
interface ModuleVersioningModelFactoryInterface
{
    /**
     * factory
     *
     * @param string $module
     *
     * @return ModuleVersioningModel
     */
    public function factory($module);
}
