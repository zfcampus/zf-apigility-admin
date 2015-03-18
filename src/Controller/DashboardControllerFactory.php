<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

class DashboardControllerFactory
{
    public function __invoke($controllers)
    {
        $services = $controllers->getServiceLocator();
        return new DashboardController(
            $services->get('ZF\Apigility\Admin\Model\AuthenticationModel'),
            $services->get('ZF\Apigility\Admin\Model\ContentNegotiationModel'),
            $services->get('ZF\Apigility\Admin\Model\DbAdapterModel'),
            $services->get('ZF\Apigility\Admin\Model\ModuleModel'),
            $services->get('ZF\Apigility\Admin\Model\RestServiceModelFactory'),
            $services->get('ZF\Apigility\Admin\Model\RpcServiceModelFactory')
        );
    }
}
