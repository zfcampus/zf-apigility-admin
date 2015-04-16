<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AppController extends AbstractActionController
{
    public function appAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate('zf-apigility-ui');
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
