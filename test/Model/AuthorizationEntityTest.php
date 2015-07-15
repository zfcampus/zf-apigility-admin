<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\AuthorizationEntity;

class AuthorizationEntityTest extends TestCase
{
    protected function getSeedValuesForEntity()
    {
        return [
            'Foo\V1\Rest\Session\Controller::__entity__' => [
                'GET' => true,
                'POST' => true,
                'PATCH' => true,
                'PUT' => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rest\Session\Controller::__collection__' => [
                'GET' => true,
                'POST' => false,
                'PATCH' => false,
                'PUT' => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rpc\Message\Controller::message' => [
                'GET' => true,
                'POST' => true,
                'PATCH' => false,
                'PUT' => false,
                'DELETE' => false,
            ],
            'Foo\V1\Rpc\Message\Controller::translate' => [
                'GET' => true,
                'POST' => true,
                'PATCH' => false,
                'PUT' => false,
                'DELETE' => false,
            ],
        ];
    }

    public function testEntityIsIterable()
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);
        $this->assertInstanceOf('Traversable', $entity);
    }

    public function testIteratingEntityReturnsAKeyForEachOfRestEntityAndCollection()
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        $this->assertContains('Foo\V1\Rest\Session\Controller::__entity__', $keys);
        $this->assertContains('Foo\V1\Rest\Session\Controller::__collection__', $keys);
    }

    public function testIteratingEntityReturnsAKeyForEachActionOfRpcController()
    {
        $values = $this->getSeedValuesForEntity();
        $entity = new AuthorizationEntity($values);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        $this->assertContains('Foo\V1\Rpc\Message\Controller::message', $keys);
        $this->assertContains('Foo\V1\Rpc\Message\Controller::translate', $keys);
    }

    public function testCanAddARestServiceAtATime()
    {
        $entity = new AuthorizationEntity();
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_ENTITY, [
            'GET' => true,
            'POST' => true,
            'PATCH' => true,
            'PUT' => false,
            'DELETE' => false,
        ]);
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_COLLECTION, [
            'GET' => true,
            'POST' => false,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ]);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        $this->assertContains('Foo\V1\Rest\Session\Controller::__entity__', $keys);
        $this->assertContains('Foo\V1\Rest\Session\Controller::__collection__', $keys);
    }

    public function testCanAddAnRpcServiceAtATime()
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message', [
            'GET' => true,
            'POST' => true,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ]);
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'translate', [
            'GET' => true,
            'POST' => true,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ]);

        $keys = [];
        foreach ($entity as $key => $value) {
            $keys[] = $key;
        }
        $this->assertContains('Foo\V1\Rpc\Message\Controller::message', $keys);
        $this->assertContains('Foo\V1\Rpc\Message\Controller::translate', $keys);
    }

    public function testCanRetrieveNamedServices()
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message', [
            'GET' => true,
            'POST' => true,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ]);
        $this->assertTrue($entity->has('Foo\V1\Rpc\Message\Controller::message'));
        $privileges = $entity->get('Foo\V1\Rpc\Message\Controller::message');
        $this->assertEquals([
            'GET' => true,
            'POST' => true,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ], $privileges);
    }

    public function testAddingARestServiceWithoutHttpMethodsProvidesDefaults()
    {
        $entity = new AuthorizationEntity();
        $entity->addRestService('Foo\V1\Rest\Session\Controller', AuthorizationEntity::TYPE_ENTITY);
        $this->assertTrue($entity->has('Foo\V1\Rest\Session\Controller::__entity__'));
        $privileges = $entity->get('Foo\V1\Rest\Session\Controller::__entity__');
        $this->assertEquals([
            'GET' => false,
            'POST' => false,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ], $privileges);
    }

    public function testAddingAnRpcServiceWithoutHttpMethodsProvidesDefaults()
    {
        $entity = new AuthorizationEntity();
        $entity->addRpcService('Foo\V1\Rpc\Message\Controller', 'message');
        $this->assertTrue($entity->has('Foo\V1\Rpc\Message\Controller::message'));
        $privileges = $entity->get('Foo\V1\Rpc\Message\Controller::message');
        $this->assertEquals([
            'GET' => false,
            'POST' => false,
            'PATCH' => false,
            'PUT' => false,
            'DELETE' => false,
        ], $privileges);
    }
}
