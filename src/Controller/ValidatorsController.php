<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\Apigility\Admin\Model\ValidatorsModel;

class ValidatorsController extends AbstractPluginManagerController
{
    protected $property = 'validators';

    public function __construct(ValidatorsModel $model)
    {
        $this->model = $model;
    }

    public function validatorsAction()
    {
        return $this->handleRequest();
    }
}
