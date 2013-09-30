<?php

namespace ZFTest\Apigility\Admin\Model;

use PHPUnit_Framework_TestCase as TestCase;
use Version;
use Zend\Config\Writer\PhpArray;
use ZF\Apigility\Admin\Model\ModuleModel;
use ZF\Apigility\Admin\Model\VersioningModel;
use ZF\Configuration\ConfigResource;

require_once __DIR__ . '/TestAsset/module/Version/Module.php';

class VersioningModelTest extends TestCase
{
    public function setUp()
    {
        $this->moduleConfigFile = __DIR__ . '/TestAsset/module/Version/config/module.config.php';
        $this->setUpModuleConfig();

        $writer      = new PhpArray();
        $config      = include $this->moduleConfigFile;
        $resource    = new ConfigResource($config, $this->moduleConfigFile, $writer);
        $this->model = new VersioningModel($resource);
    }

    public function tearDown()
    {
        $this->removeModuleConfig();
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    }

    public function removeModuleConfig()
    {
        if (file_exists($this->moduleConfigFile)) {
            unlink($this->moduleConfigFile);
        }
    }

    public function setUpModuleConfig()
    {
        $this->removeModuleConfig();
        copy($this->moduleConfigFile . '.dist', $this->moduleConfigFile);
    }

    /**
     * Remove a directory even if not empty (recursive delete)
     *
     * @param  string $dir
     * @return boolean
     */
    public function removeDir($dir)
    {
        if (!file_exists($dir)) {
            return false;
        }
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = "$dir/$file";
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }
        return rmdir($dir);
    }

    public function testGetModuleVersions()
    {
        $versions = $this->model->getModuleVersions('Version', __DIR__ . '/TestAsset');
        $this->assertEquals(array(1), $versions);
    }

    public function testCreateVersion()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset');

        $this->assertTrue($result);
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest"));

        $config = include($this->moduleConfigFile);
        $this->assertArrayHasKey('router', $config);
        $this->assertEquals('Version\\V1\\Rest\Message\Controller', $config['router']['routes']['version.rest.message']['options']['defaults']['controller']);
        $this->assertEquals('Version\\V1\\Rest\Comment\Controller', $config['router']['routes']['version.rest.comment']['options']['defaults']['controller']);

        $this->assertArrayHasKey('zf-rest', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['zf-rest']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['zf-rest'], var_export($config, 1));
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageResource', $config['zf-rest']['Version\\V1\\Rest\\Message\\Controller']['listener']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageResource', $config['zf-rest']['Version\\V2\\Rest\\Message\\Controller']['listener']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageEntity', $config['zf-rest']['Version\\V1\\Rest\\Message\\Controller']['entity_class']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageEntity', $config['zf-rest']['Version\\V2\\Rest\\Message\\Controller']['entity_class']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageCollection', $config['zf-rest']['Version\\V1\\Rest\\Message\\Controller']['collection_class']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageCollection', $config['zf-rest']['Version\\V2\\Rest\\Message\\Controller']['collection_class']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentResource', $config['zf-rest']['Version\\V1\\Rest\\Comment\\Controller']['listener']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentResource', $config['zf-rest']['Version\\V2\\Rest\\Comment\\Controller']['listener']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentEntity', $config['zf-rest']['Version\\V1\\Rest\\Comment\\Controller']['entity_class']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentEntity', $config['zf-rest']['Version\\V2\\Rest\\Comment\\Controller']['entity_class']);
        $this->assertEquals('Version\\V1\\Rest\\Comment\\CommentCollection', $config['zf-rest']['Version\\V1\\Rest\\Comment\\Controller']['collection_class']);
        $this->assertEquals('Version\\V2\\Rest\\Comment\\CommentCollection', $config['zf-rest']['Version\\V2\\Rest\\Comment\\Controller']['collection_class']);

        $this->assertArrayHasKey('zf-content-negotiation', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['zf-content-negotiation']['controllers']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['zf-content-negotiation']['controllers']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['zf-content-negotiation']['accept-whitelist']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['zf-content-negotiation']['accept-whitelist']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\Controller', $config['zf-content-negotiation']['content-type-whitelist']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\Controller', $config['zf-content-negotiation']['content-type-whitelist']);

        // Check if the mediatype of the new version is correct
        $acceptWhitelist = $config['zf-content-negotiation']['accept-whitelist'];
        $this->assertContains('application/version.v2+json', $acceptWhitelist['Version\\V2\\Rest\\Message\\Controller']);
        $contentTypeWhitelist = $config['zf-content-negotiation']['content-type-whitelist'];
        $this->assertContains('application/version.v2+json', $contentTypeWhitelist['Version\\V2\\Rest\\Message\\Controller']);

        $this->assertArrayHasKey('zf-hal', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageEntity', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageEntity', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageCollection', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageCollection', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentEntity', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentEntity', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Comment\\CommentCollection', $config['zf-hal']['metadata_map']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Comment\\CommentCollection', $config['zf-hal']['metadata_map']);

        $this->assertArrayHasKey('zf-apigility', $config);
        $this->assertArrayHasKey('Version\\V1\\Rest\\Message\\MessageResource', $config['zf-apigility']['db-connected']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\Controller', $config['zf-apigility']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['controller_service_name']);
        $this->assertEquals('Version\\V1\\Rest\\Message\\MessageResource\\Table', $config['zf-apigility']['db-connected']['Version\\V1\\Rest\\Message\\MessageResource']['table_service']);
        $this->assertArrayHasKey('Version\\V2\\Rest\\Message\\MessageResource', $config['zf-apigility']['db-connected']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\Controller', $config['zf-apigility']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['controller_service_name']);
        $this->assertEquals('Version\\V2\\Rest\\Message\\MessageResource\\Table', $config['zf-apigility']['db-connected']['Version\\V2\\Rest\\Message\\MessageResource']['table_service']);

        $this->assertArrayHasKey('service_manager', $config);
        $this->assertEquals('Version\V1\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\Model']);
        $this->assertEquals('Version\V1\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V1\Rest\Comment\CommentResource']);
        $this->assertEquals('Version\V2\Rest\Comment\CommentModelFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\Model']);
        $this->assertEquals('Version\V2\Rest\Comment\CommentResourceFactory', $config['service_manager']['factories']['Version\V2\Rest\Comment\CommentResource']);
        
        $this->removeDir(__DIR__ . "/TestAsset/module/Version/src/Version/V2");
    } 

    public function testCreateVersionRenamesNamespacesInCopiedClasses()
    {
        $result = $this->model->createVersion('Version', 2, __DIR__ . '/TestAsset');
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php"));
        $this->assertTrue(file_exists(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php"));

        $nsSep      = preg_quote('\\');
        $pattern1 = sprintf(
            '#Version%sV1%s#',
            $nsSep,
            $nsSep
        );
        $pattern2 = str_replace('1', '2', $pattern1);

        $controller = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rpc/Bar/BarController.php");
        $this->assertNotRegExp($pattern1, $controller);
        $this->assertRegExp($pattern2, $controller);

        $entity = file_get_contents(__DIR__ . "/TestAsset/module/Version/src/Version/V2/Rest/Foo/FooEntity.php");
        $this->assertNotRegExp($pattern1, $entity);
        $this->assertRegExp($pattern2, $entity);
    }
}
