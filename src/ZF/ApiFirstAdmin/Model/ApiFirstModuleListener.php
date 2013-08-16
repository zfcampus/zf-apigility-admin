<?php

namespace ZF\ApiFirstAdmin\Model;

use ZF\ApiProblem\ApiProblem;
use ZF\Rest\AbstractResourceListener;
use ZF\Rest\Exception\CreationException;

class ApiFirstModuleListener extends AbstractResourceListener
{
    /**
     * @var ApiFirstModule
     */
    protected $modules;

    /**
     * @var string
     */
    protected $modulePath = '.';

    /**
     * @param ApiFirstModule $modules 
     */
    public function __construct(ApiFirstModule $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Set path to use when creating new modules
     * 
     * @param  string $path 
     * @return self
     * @throws InvalidArgumentException for invalid paths
     */
    public function setModulePath($path)
    {
        if (!is_dir($path)) {
            throw new InvalidArgumentException(sprintf(
                'Invalid module path "%s"; does not exist',
                $path
            ));
        }
        $this->modulePath = $path;
        return $this;
    }

    /**
     * Create a new API-First enabled module
     * 
     * @param  array|object $data 
     * @return ModuleMetadata
     * @throws CreationException
     */
    public function create($data)
    {
        if (!isset($data['name'])) {
            throw new CreationException('Missing module name');
        }

        $name = $data['name'];
        $name = str_replace('.', '\\', $name);
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*(\\\+[a-zA-Z][a-zA-Z0-9_]*)?$/', $name)) {
            throw new CreationException('Invalid module name; must be a valid PHP namespace name');
        }

        if (false === $this->modules->createModule($module, $this->modulePath)) {
            throw new CreationException('Unable to create module; check your paths and permissions');
        }

        $metadata = new ModuleMetadata($name);
        return $metadata;
    }

    /**
     * Fetch module metadata
     * 
     * @param  string $id 
     * @return ModuleMetadata|ApiProblem
     */
    public function fetch($id)
    {
        $module = $this->modules->getModule($id);
        if (!$module instanceof ModuleMetadata) {
            return new ApiProblem(404, 'Module not found or is not API-First enabled');
        }
        return $module;
    }

    /**
     * Fetch metadata for all API-First enabled modules
     * 
     * @param  array $params 
     * @return array
     */
    public function fetchAll($params = array())
    {
        return $this->modules->getModules();
    }
}
