<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AutodiscoveryControllerFactory
 *
 * @package ZF\Apigility\Admin\Controller
 */
class DbAutodiscoveryControllerFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $controllers
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $controllers)
    {
        $services = $controllers->getServiceLocator();
        /** @var \ZF\Apigility\Admin\Model\DbAutodiscoveryModel $model */
        $model = $services->get('ZF\Apigility\Admin\Model\DbAutodiscoveryModel');
        return new DbAutodiscoveryController($model);
    }
}
