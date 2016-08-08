<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Factory;

use Interop\Container\ContainerInterface;
use Zend\InputFilter\Factory as InputFilterFactory;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Apigility\Admin\InputFilter\InputFilterInputFilter;

class InputFilterInputFilterFactory implements FactoryInterface
{
    /**
     * Create and return an InputFilterInputFilter instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return InputFilterInputFilter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $factory = new InputFilterFactory();
        $factory->setInputFilterManager($container->get('InputFilterManager'));
        $factory->getDefaultFilterChain()->setPluginManager($container->get('FilterManager'));
        $factory->getDefaultValidatorChain()->setPluginManager($container->get('ValidatorManager'));

        return new InputFilterInputFilter($factory);
    }

    /**
     * Create and return an InputFilterInputFilter instance.
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return InputFilterInputFilter
     */
    public function createService(ServiceLocatorInterface $container)
    {
        if ($container instanceof AbstractPluginManager) {
            $container = $container->getServiceLocator() ?: $container;
        }
        return $this($container, InputFilterInputFilter::class);
    }
}
