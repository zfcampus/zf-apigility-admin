<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Factory;

use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\InputFilter\InputFilterInputFilter;

class InputFilterInputFilterFactory implements FactoryInterface
{
    /**
     * Create service
     *
     * @param ServiceLocatorInterface $inputFilters
     * @return InputFilterInputFilter
     */
    public function createService(ServiceLocatorInterface $inputFilters)
    {
        $services = $inputFilters->getServiceLocator();
        $factory  = new InputFilterFactory();
        $factory->setInputFilterManager($inputFilters);
        $factory->getDefaultFilterChain()->setPluginManager($services->get('FilterManager'));
        $factory->getDefaultValidatorChain()->setPluginManager($services->get('ValidatorManager'));

        return new InputFilterInputFilter($factory);
    }
}
