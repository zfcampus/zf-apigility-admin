<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Interop\Container\ContainerInterface;
use Zend\Hydrator\Strategy\StrategyInterface;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\View\ApiProblemModel;

class StrategyController extends AbstractActionController
{
    /**
     * @param ContainerInterface
     */
    protected $serviceLocator;

    /**
     * @param ContainerInterface $serviceLocator
     */
    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    public function existsAction()
    {
        $container = $this->getServiceLocator();
        $strategyName = $this->params()->fromRoute('strategy_name', false);

        if (! $container->has($strategyName)) {
            return new ApiProblemModel(new ApiProblem(422, 'This service was not found in the service manager'));
        }

        if (! $container->get($strategyName) instanceof StrategyInterface) {
            return new ApiProblemModel(new ApiProblem(422, 'This service does not implement StrategyInterface'));
        }

        return ['exists' => true];
    }
}
