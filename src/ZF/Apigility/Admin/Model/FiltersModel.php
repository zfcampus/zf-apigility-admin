<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\FilterPluginManager;
use Zend\ServiceManager\ServiceManager;
use ZF\Apigility\Admin\Exception;

class FiltersModel extends AbstractPluginManagerModel
{
    /**
     * @var array
     */
    protected $metadata;

    /**
     * $pluginManager should be an instance of
     * Zend\Filter\FilterPluginManager.
     *
     * @param ServiceManager $pluginManager
     * @param array $metadata
     */
    public function __construct(ServiceManager $pluginManager, array $metadata = array())
    {
        if (! $pluginManager instanceof FilterPluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                '%s expects an instance of Zend\Filter\FilterPluginManager; received "%s"',
                __CLASS__,
                get_class($pluginManager)
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

        $plugins  = parent::getPlugins();
        $plugins  = array_flip($plugins);
        $metadata = $this->metadata;

        array_walk($plugins, function (& $value, $key) use ($metadata) {
            if (! array_key_exists($key, $metadata)) {
                $value = array();
                return;
            }
            $value = $metadata[$key];
        });

        $this->plugins = $plugins;
        return $this->plugins;
    }
}
