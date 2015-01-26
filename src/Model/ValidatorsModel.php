<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Validator\ValidatorPluginManager;
use Zend\ServiceManager\ServiceManager;
use ZF\Apigility\Admin\Exception;

class ValidatorsModel extends AbstractPluginManagerModel
{
    /**
     * @var ValidatorMetadataModel
     */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Zend\Validator\ValidatorPluginManager.
     *
     * @param ServiceManager $pluginManager
     */
    public function __construct(ServiceManager $pluginManager, ValidatorMetadataModel $metadata = null)
    {
        if (! $pluginManager instanceof ValidatorPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Zend\Validator\ValidatorPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
            ));
        }

        if (null === $metadata) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Zend\Validator\ValidatorMetadataModel'
                . ' as the second argument to the constructor',
                __CLASS__
            ));
        }

        parent::__construct($pluginManager);
        $this->metadata = $metadata;
    }

    /**
     * Retrieve all plugins
     *
     * Merges the list of plugins with the plugin metadata
     *
     * @return array
     */
    protected function getPlugins()
    {
        if (is_array($this->plugins)) {
            return $this->plugins;
        }

        $plugins = parent::getPlugins();
        $plugins = array_flip($plugins);
        $plugins = array_merge($plugins, $this->metadata->fetchAll());
        array_walk($plugins, function (& $value) {
            if (is_array($value)) {
                return;
            }
            $value = array(
                'breakchainonfailure' => 'bool',
            );
        });
        $this->plugins = $plugins;
        return $this->plugins;
    }
}
