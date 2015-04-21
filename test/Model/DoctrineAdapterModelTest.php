<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\DoctrineAdapterModel;
use ZF\Configuration\ConfigResource;

class DoctrineAdapterModelTest extends TestCase
{
    public function getMockWriter()
    {
        return $this->getMock('Zend\Config\Writer\WriterInterface');
    }

    public function getGlobalConfig()
    {
        return new ConfigResource(array(
            'doctrine' => array(
                'entitymanager' => array(
                    'orm_default' => array(
                    ),
                ),
                'documentationmanager' => array(
                    'odm_default' => array(
                    ),
                ),
            ),
        ), 'php://temp', $this->getMockWriter());
    }

    public function getLocalConfig()
    {
        return new ConfigResource(array(
            'doctrine' => array(
                'connection' => array(
                    'orm_default' => array(
                        'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                        'params' => array(),
                    ),
                    'odm_default' => array(
                        'connectionString' => 'mongodb://localhost:27017',
                        'options' => array(),
                    ),
                    'odm_dbname' => array(
                        'dbname' => 'test',
                        'options' => array(),
                    ),
                ),
            ),
        ), 'php://temp', $this->getMockWriter());
    }

    public function testFetchAllReturnsMixOfOrmAndOdmAdapters()
    {
        $model = new DoctrineAdapterModel($this->getGlobalConfig(), $this->getLocalConfig());
        $adapters = $model->fetchAll();
        $this->assertInternalType('array', $adapters);

        foreach ($adapters as $adapter) {
            $this->assertInstanceOf('ZF\Apigility\Admin\Model\DoctrineAdapterEntity', $adapter);
            $data = $adapter->getArrayCopy();
            $this->assertArrayHasKey('adapter_name', $data);
            if (strrpos($data['adapter_name'], 'odm_')) {
                $this->assertContains('documentmanager', $data['adapter_name']);
            } elseif (strrpos($data['adapter_name'], 'orm_default')) {
                $this->assertContains('entitymanager', $data['adapter_name']);
            }
        }
    }
}
