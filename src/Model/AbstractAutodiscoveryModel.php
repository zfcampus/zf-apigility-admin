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
    protected $validators = array(
        'text' => array(
            'name' => 'Zend\Validator\StringLength',
            'options' => array(
                'min' => 1,
                'max' => 1,
            ),
        ),
        'unique' => array(
            'name' => 'ZF\ContentValidation\Validator\DbNoRecordExists',
            'options' => array(),
        ),
        'foreign_key' => array(
            'name' => 'ZF\ContentValidation\Validator\DbRecordExists',
            'options' => array(),
        ),
    );

    /**
     * @var array
     */
    protected $filters = array(
        'text' => array(
            array('name' => 'Zend\Filter\StringTrim'),
            array('name' => 'Zend\Filter\StripTags'),
        ),
        'integer' => array(
            array('name' => 'Zend\Filter\StripTags'),
            array('name' => 'Zend\Filter\Digits'),
        ),
    );

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
