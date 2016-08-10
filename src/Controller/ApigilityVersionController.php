<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class ApigilityVersionController extends AbstractActionController
{
    public function indexAction()
    {
        return new JsonModel([
            'version' => defined('Apigility\VERSION') ? \Apigility\VERSION : '@dev',
        ]);
    }
}
