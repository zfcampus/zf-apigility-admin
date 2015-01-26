<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DocumentationModelFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $services)
    {
        if (! $services->has('ZF\Configuration\ConfigResourceFactory')) {
            throw new ServiceNotCreatedException(sprintf(
                '%s\\InputFilterModel requires that the ZF\Configuration\ConfigResourceFactory'
                . ' service be present; service not found',
                __NAMESPACE__
            ));
        }
        return new DocumentationModel(
            $services->get('ZF\Configuration\ConfigResourceFactory'),
            $services->get('ZF\Configuration\ModuleUtils')
        );
    }
}
