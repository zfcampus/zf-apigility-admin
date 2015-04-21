<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\DoctrineAdapterEntity;

class DoctrineAdapterEntityTest extends TestCase
{
    public function testCanRepresentAnOrmEntity()
    {
        $config = array(
            'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
            'params' => array(),
        );
        $entity = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.entitymanager.test', $serialized['adapter_name']);
    }

    public function testCanRepresentAnOdmEntity()
    {
        $config = array(
            'connectionString' => 'mongodb://localhost:27017',
        );
        $entity = new DoctrineAdapterEntity('test', $config);
        $serialized = $entity->getArrayCopy();

        $this->assertArrayHasKey('adapter_name', $serialized);
        $this->assertEquals('doctrine.documentmanager.test', $serialized['adapter_name']);
    }
}
