<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014-2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZF\Configuration\ConfigResourceFactory;
use ZF\Configuration\ModuleUtils;

class DocumentationModelFactory implements FactoryInterface
{
    /**
     * Create and return a DocumentationModel instance.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return DocumentationModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! $container->has(ConfigResourceFactory::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s requires that the %s service be present; service not found',
                DocumentationModel::class,
                ConfigResourceFactory::class
            ));
        }
        return new DocumentationModel(
            $container->get(ConfigResourceFactory::class),
            $container->get(ModuleUtils::class)
        );
    }

    /**
     * Create and return a DocumentationModel instance (v2).
     *
     * Provided for backwards compatibility; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return DocumentationModel
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, DocumentationModel::class);
    }
}
