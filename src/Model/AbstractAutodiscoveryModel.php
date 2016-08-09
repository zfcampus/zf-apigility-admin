<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Model;

use Zend\Filter\StaticFilter;
use Zend\ServiceManager\ServiceLocatorInterface;
use Exception;

/**
 * This class is instantiated with a $config in some implementations (DbAutodiscoveryModel)
 * but this is dependent on the root service locator for the moduleHasService call below
 * and that must be injected into any class extending this abstract.
 */
abstract class AbstractAutodiscoveryModel
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
            'name' => \Zend\Validator\StringLength::class,
            'options' => [
                'min' => 1,
                'max' => 1,
            ],
        ],
        'unique' => [
            'name' => \ZF\ContentValidation\Validator\DbNoRecordExists::class,
            'options' => [],
        ],
        'foreign_key' => [
            'name' => \ZF\ContentValidation\Validator\DbRecordExists::class,
            'options' => [],
        ],
    ];

    /**
     * @var array
     */
    protected $filters = [
        'text' => [
            ['name' => \Zend\Filter\StringTrim::class],
            ['name' => \Zend\Filter\StripTags::class],
        ],
        'integer' => [
            ['name' => \Zend\Filter\StripTags::class],
            ['name' => \Zend\Filter\Digits::class],
        ],
    ];

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     * @throws Exception if no service locator is composed
     */
    public function getServiceLocator()
    {
        if (! $this->serviceLocator) {
            throw new Exception('The AbstractAutodiscoveryModel must be composed with a service locator');
        }

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
        $resourceClass = sprintf(
            '%s\\V%s\\Rest\\%s\\%sResource',
            $module,
            $version,
            $resourceName,
            $resourceName
        );
        return $this->getServiceLocator()->has($resourceClass);
    }
}
