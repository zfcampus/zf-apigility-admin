<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use ZF\Configuration\ConfigResource;

class ContentNegotiationModel
{
    /**
     * @var ConfigResource
     */
    protected $globalConfig;

    /**
     * @param ConfigResource $globalConfig
     */
    public function __construct(ConfigResource $globalConfig)
    {
        $this->globalConfig = $globalConfig;
    }

    /**
     * Create Content Negotiation configuration
     *
     * @param  mixed $name
     * @param  array $contentConfig
     * @return ContentNegotiationEntity
     */
    public function create($name, array $contentConfig)
    {
        $key = 'zf-content-negotiation.selectors.' . $name;
        $this->globalConfig->patchKey($key, $contentConfig);
        return new ContentNegotiationEntity($name, $contentConfig);
    }

    /**
     * Update an existing Content Negotiation
     *
     * @param  string $name
     * @param  array $contentConfig
     * @return ContentNegotiationEntity
     */
    public function update($name, array $contentConfig)
    {
        return $this->create($name, $contentConfig);
    }

    /**
     * Remove a Content Negotiation
     *
     * @param  string $name
     * @return true
     */
    public function remove($name)
    {
        $key =  'zf-content-negotiation.selectors.' . $name;
        $this->globalConfig->deleteKey($key);
        return true;
    }

    /**
     * Retrieve all content negotiations
     *
     * @return array
     */
    public function fetchAll()
    {
        $config = array();
        $fromConfigFile = $this->globalConfig->fetch(true);
        if (isset($fromConfigFile['zf-content-negotiation']['selectors'])
            && is_array($fromConfigFile['zf-content-negotiation']['selectors'])
        ) {
            $config = $fromConfigFile['zf-content-negotiation']['selectors'];
        }

        $negotiations = array();
        foreach ($config as $name => $contentConfig) {
            $negotiations[] = new ContentNegotiationEntity($name, $contentConfig);
        }

        return $negotiations;
    }

    /**
     * Fetch configuration details for a named adapter
     *
     * @param  string $name
     * @return ContentNegotiationEntity
     */
    public function fetch($name)
    {
        $config = $this->globalConfig->fetch(true);
        if (!isset($config['zf-content-negotiation']['selectors'][$name])
            || !is_array($config['zf-content-negotiation']['selectors'][$name])
        ) {
            return false;
        }

        return new ContentNegotiationEntity($name, $config['zf-content-negotiation']['selectors'][$name]);
    }
}
