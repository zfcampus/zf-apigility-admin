<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\Apigility\Admin\Model\HydratorsModel;

class HydratorsController extends AbstractPluginManagerController
{
    protected $property = 'hydrators';

    public function __construct(HydratorsModel $model)
    {
        $this->model = $model;
    }

    public function hydratorsAction()
    {
        return $this->handleRequest();
    }
}
