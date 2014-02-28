<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ValidatorMetadataModelFactory implements FactoryInterface
{
    /**
     * Return one of the plugin manager model instances
     *
     * @param ServiceLocatorInterface $services
     * @return ValidatorMetadataModel
     */
    public function createService(ServiceLocatorInterface $services)
    {
        $metadata = array();
        if ($services->has('Config')) {
            $config = $services->get('Config');
            if (isset($config['validator_metadata'])) {
                $metadata = $config['validator_metadata'];
            }
        }

        return new ValidatorMetadataModel($metadata);
    }
}
