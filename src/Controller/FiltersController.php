<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\Apigility\Admin\Model\FiltersModel;

class FiltersController extends AbstractPluginManagerController
{
    protected $property = 'filters';

    public function __construct(FiltersModel $model)
    {
        $this->model = $model;
    }

    public function filtersAction()
    {
        return $this->handleRequest();
    }
}
