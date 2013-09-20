<?php

namespace ZF\Apigility\Admin\Model;

use InvalidArgumentException;
use ReflectionClass;

class ModuleEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var bool
     */
    protected $isVendor;

    /**
     * @var array
     */
    protected $restServices;

    /**
     * @var array
     */
    protected $rpcServices;

    /**
     * @param  string $name
     * @param  array $restServices
     * @param  array $rpcServices
     * @param  bool $isVendor
     * @throws InvalidArgumentException for modules that do not exist
     */
    public function __construct($namespace, array $restServices = array(), array $rpcServices = array(), $isVendor = null)
    {
        if (!class_exists($namespace . '\\Module')) {
            throw new InvalidArgumentException(sprintf(
                'Invalid module "%s"; no Module class exists for that module',
                $namespace
            ));
        }

        $this->name          = $this->normalizeName($namespace);
        $this->namespace     = $namespace;
        $this->restServices = $restServices;
        $this->rpcServices  = $rpcServices;
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
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
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
    public function getRestServices()
    {
        return $this->restServices;
    }

    /**
     * @return array
     */
    public function getRpcServices()
    {
        return $this->rpcServices;
    }

    /**
     * Populate object from array
     *
     * @param  array $data
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            switch (strtolower($key)) {
                case 'module':
                case 'name':
                    $this->name = $value;
                    break;
                case 'namespace':
                    $this->namespace = $value;
                    break;
                case 'isvendor':
                case 'is_vendor':
                    $this->isVendor = (bool) $value;
                    break;
                case 'rest':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(
                            'REST services must be an array; received "%s"',
                            (is_object($value) ? get_class($value) : gettype($value))
                        );
                    }
                    $this->restServices = $value;
                    break;
                case 'rpc':
                    if (!is_array($value)) {
                        throw new InvalidArgumentException(
                            'RPC services must be an array; received "%s"',
                            (is_object($value) ? get_class($value) : gettype($value))
                        );
                    }
                    $this->rpcServices = $value;
                    break;
                default:
                    break;
            }
        }
    }

    /**
     * Retrieve array representation
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return array(
            'name'      => $this->name,
            'namespace' => $this->namespace,
            'is_vendor' => $this->isVendor(),
            'rest'      => $this->getRestServices(),
            'rpc'       => $this->getRpcServices(),
        );
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
        $r = new ReflectionClass($this->namespace . '\\Module');
        $filename = $r->getFileName();
        if (preg_match('#[/\\\\]vendor[/\\\\]#', $filename)) {
            $this->isVendor = true;
            return;
        }
        $this->isVendor = false;
    }

    /**
     * normalizeName
     *
     * @param mixed $namespace
     * @return void
     */
    protected function normalizeName($namespace)
    {
        return str_replace('\\', '.', $namespace);
    }

    protected function deriveNamespace($name)
    {
        return str_replace('.', '\\', $namespace);
    }
}
