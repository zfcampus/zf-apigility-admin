<?php

namespace ZF\Apigility\Admin\InputFilter\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\InputFilter\InputFilterInputFilter;

class InputFilterInputFilterFactory implements FactoryInterface
{

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new InputFilterInputFilter($serviceLocator);
    }

}
 