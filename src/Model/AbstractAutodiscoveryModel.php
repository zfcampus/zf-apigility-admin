<?php

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\StaticFilter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractAutodiscoveryModel implements ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $validators = [
        'text' => [
            'name' => 'Zend\Validator\StringLength',
            'options' => [
                'min' => 1,
                'max' => 1,
            ],
        ],
        'unique' => [
            'name' => 'ZF\ContentValidation\Validator\DbNoRecordExists',
            'options' => [],
        ],
        'foreign_key' => [
            'name' => 'ZF\ContentValidation\Validator\DbRecordExists',
            'options' => [],
        ],
    ];

    /**
     * @var array
     */
    protected $filters = [
        'text' => [
            ['name' => 'Zend\Filter\StringTrim'],
            ['name' => 'Zend\Filter\StripTags'],
        ],
        'integer' => [
            ['name' => 'Zend\Filter\StripTags'],
            ['name' => 'Zend\Filter\Digits'],
        ],
    ];

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return AbstractAutodiscoveryModel
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Constructor
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $module
     * @param $version
     * @param $tableName
     * @return bool
     */
    protected function moduleHasService($module, $version, $tableName)
    {
        $resourceName = StaticFilter::execute($tableName, 'WordUnderscoreToCamelCase');
        $resourceClass     = sprintf(
            '%s\\V%s\\Rest\\%s\\%sResource',
            $module,
            $version,
            $resourceName,
            $resourceName
        );
        return $this->getServiceLocator()->has($resourceClass);
    }
}
