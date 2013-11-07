<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2013 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use ZF\Apigility\Admin\Model\AuthenticationEntity;

class AuthenticationEntityTest extends TestCase
{
    public function testIsBasicByDefault()
    {
        $entity = new AuthenticationEntity();
        $this->assertTrue($entity->isBasic());
        $this->assertFalse($entity->isDigest());
    }

    public function testRealmHasADefaultValue()
    {
        $entity = new AuthenticationEntity();
        $this->assertAttributeEquals('api', 'realm', $entity);
    }

    public function testCanSpecifyTypeDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST);
        $this->assertFalse($entity->isBasic());
        $this->assertTrue($entity->isDigest());
    }

    public function testCanSpecifyRealmDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'zendcon');
        $this->assertAttributeEquals('zendcon', 'realm', $entity);
    }

    public function testCanSetBasicParametersDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'zendcon', array(
            'htpasswd' => __DIR__ . '/htpasswd',
            'htdigest' => __DIR__ . '/htdigest',
        ));
        $this->assertAttributeEquals(__DIR__ . '/htpasswd', 'htpasswd', $entity);
        $this->assertAttributeEmpty('htdigest', $entity);
    }

    public function testCanSetDigestParametersDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST, 'zendcon', array(
            'htpasswd'       => __DIR__ . '/htpasswd',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ));
        $this->assertAttributeEmpty('htpasswd', $entity);
        $this->assertAttributeEquals(__DIR__ . '/htdigest', 'htdigest', $entity);
        $this->assertAttributeEquals(3600, 'nonceTimeout', $entity);
        $this->assertAttributeEquals('/api', 'digestDomains', $entity);
    }

    public function testSerializationOfBasicAuthReturnsOnlyKeysSpecificToType()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'zendcon', array(
            'htpasswd' => __DIR__ . '/htpasswd',
            'htdigest' => __DIR__ . '/htdigest',
        ));
        $this->assertEquals(array(
            'accept_schemes' => array('basic'),
            'realm'          => 'zendcon',
            'htpasswd'       => __DIR__ . '/htpasswd',
        ), $entity->getArrayCopy());
    }

    public function testSerializationOfDigestAuthReturnsOnlyKeysSpecificToType()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_DIGEST, 'zendcon', array(
            'htpasswd'       => __DIR__ . '/htpasswd',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ));
        $this->assertEquals(array(
            'accept_schemes' => array('digest'),
            'realm'          => 'zendcon',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ), $entity->getArrayCopy());
    }
}
