<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use ZF\Configuration\ConfigResource;
use ZF\Configuration\ConfigWriter;

class DoctrineAdapterModelFactory
{
    /**
     * @param ContainerInterface $container
     * @return DoctrineAdapterModel
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container)
    {
        if (! $container->has('config')) {
            throw new ServiceNotCreatedException(sprintf(
                'Cannot create %s service because config service is not present',
                DbAdapterModel::class
            ));
        }

        $config = $container->get('config');
        $writer = $container->get(ConfigWriter::class);

        return new DoctrineAdapterModel(
            new ConfigResource($config, 'config/autoload/doctrine.global.php', $writer),
            new ConfigResource($config, 'config/autoload/doctrine.local.php', $writer)
        );
    }
}
