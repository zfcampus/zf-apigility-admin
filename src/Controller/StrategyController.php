<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Stdlib\Hydrator\Strategy\StrategyInterface;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class StrategyController extends AbstractActionController
{
    public function existsAction()
    {
        $strategy_name = $this->params()->fromRoute('strategy_name', false);
        if ($this->getServiceLocator()->has($strategy_name)) {
            if ($this->getServiceLocator()->get($strategy_name) instanceof StrategyInterface) {
                return array('exists' => true);
            } else {
                return new ApiProblemModel(new ApiProblem(422, 'This service does not implement StrategyInterface'));
            }
        } else {
            return new ApiProblemModel(new ApiProblem(422, 'This service was not found in the service manager'));
        }
    }
}
