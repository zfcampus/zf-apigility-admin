<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\ServiceManager\ServiceManager;

class AbstractPluginManagerModel
{
    /**
     * @var array
     */
    protected $plugins;

    /**
     * @var ServiceManager
     */
    protected $pluginManager;

    /**
     * $pluginManager should typically be an instance of
     * Zend\ServiceManager\AbstractPluginManager.
     *
     * @param ServiceManager $pluginManager
     */
    public function __construct(ServiceManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return $this->getPlugins();
    }

    /**
     * Lazy loads plugins from attached plugin manager and sorts them by name
     *
     * @return array
     */
    protected function getPlugins()
    {
        if (is_array($this->plugins)) {
            return $this->plugins;
        }

        // Add invokableClasses via reflection
        $reflClass = new \ReflectionClass($this->pluginManager);
        $reflProp  = $reflClass->getProperty('invokableClasses');
        $reflProp->setAccessible(true);

        $invokables = array_flip($reflProp->getValue($this->pluginManager));
        $plugins    = array_merge($invokables, $this->pluginManager->getCanonicalNames());

        foreach ($plugins as $name => $canonical) {
            $this->plugins[] = $name;
        }

        sort($this->plugins, SORT_STRING);
        return $this->plugins;
    }
}
