<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
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
        $this->assertFalse($entity->isOAuth2());
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
        $this->assertFalse($entity->isOAuth2());
    }

    public function testCanSpecifyOauth2TypeDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2);
        $this->assertFalse($entity->isBasic());
        $this->assertFalse($entity->isDigest());
        $this->assertTrue($entity->isOAuth2());
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

    public function testCanSetOAuth2ParametersDuringInstantiation()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, array(
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ));
        $this->assertAttributeEmpty('htpasswd', $entity);
        $this->assertAttributeEmpty('htdigest', $entity);
        $this->assertAttributeEmpty('realm', $entity);
        $this->assertAttributeEquals('sqlite::memory:', 'dsn', $entity);
        $this->assertAttributeEquals('me', 'username', $entity);
        $this->assertAttributeEquals('too', 'password', $entity);
        $this->assertAttributeEquals('/api/oauth', 'routeMatch', $entity);
    }

    public function testSerializationOfBasicAuthReturnsOnlyKeysSpecificToType()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_BASIC, 'zendcon', array(
            'htpasswd' => __DIR__ . '/htpasswd',
            'htdigest' => __DIR__ . '/htdigest',
        ));
        $this->assertEquals(array(
            'type'           => 'http_basic',
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
            'type'           => 'http_digest',
            'accept_schemes' => array('digest'),
            'realm'          => 'zendcon',
            'htdigest'       => __DIR__ . '/htdigest',
            'nonce_timeout'  => 3600,
            'digest_domains' => '/api',
        ), $entity->getArrayCopy());
    }

    public function testSerializationOfOauth2AuthReturnsOnlyKeysSpecificToType()
    {
        $entity = new AuthenticationEntity(AuthenticationEntity::TYPE_OAUTH2, array(
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ));
        $this->assertEquals(array(
            'type'        => 'oauth2',
            'dsn_type'    => 'PDO',
            'dsn'         => 'sqlite::memory:',
            'username'    => 'me',
            'password'    => 'too',
            'route_match' => '/api/oauth',
        ), $entity->getArrayCopy());
    }
}
