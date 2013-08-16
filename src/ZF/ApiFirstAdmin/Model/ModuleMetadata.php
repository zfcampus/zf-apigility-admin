<?php

namespace ZF\ApiFirstAdmin\Model;

use InvalidArgumentException;
use ReflectionClass;

class ModuleMetadata
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $isVendor;

    /**
     * @var array
     */
    protected $restEndpoints;

    /**
     * @var array
     */
    protected $rpcEndpoints;

    /**
     * @param  string $name 
     * @param  array $restEndpoints 
     * @param  array $rpcEndpoints 
     * @param  bool $isVendor 
     * @throws InvalidArgumentException for modules that do not exist
     */
    public function __construct($name, array $restEndpoints = array(), array $rpcEndpoints = array(), $isVendor = null)
    {
        if (!class_exists($name . '\\Module')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid module "%s"; no Module class exists for that module',
                $name
            ));
        }

        $this->name          = $name;
        $this->restEndpoints = $restEndpoints;
        $this->rpcEndpoints  = $rpcEndpoints;
        $this->isVendor      = is_bool($isVendor) ? $isVendor : null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isVendor()
    {
        if (null === $this->isVendor) {
            $this->determineVendorStatus();
        }
        return $this->isVendor;
    }

    /**
     * @return array
     */
    public function getRestEndpoints()
    {
        return $this->restEndpoints;
    }

    /**
     * @return array
     */
    public function getRpcEndpoints()
    {
        return $this->rpcEndpoints;
    }

    /**
     * Determine whether or not a module is a vendor module
     *
     * Use ReflectionClass to determine the filename, and then checks if the
     * module lives in a vendor subdirectory.
     *
     * @todo   Add other criteria, such as "library"?
     */
    protected function determineVendorStatus()
    {
        $r = new ReflectionClass($this->name . '\\Module');
        $filename = $r->getFileName();
        if (preg_match('#[/\\\\]vendor[/\\\\]#', $filename)) {
            $this->isVendor = true;
            return;
        }
        $this->isVendor = false;
    }
}
