<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2016 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZFTest\Apigility\Admin\Listener;

use Closure;
use Interop\Container\ContainerInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Prophecy\Argument;
use ReflectionProperty;
use stdClass;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Admin\Listener\InjectModuleResourceLinksListener;
use ZF\Apigility\Admin\Model\InputFilterEntity;
use ZF\Apigility\Admin\Model\ModuleEntity;
use ZF\Apigility\Admin\Model\RestServiceEntity;
use ZF\Apigility\Admin\Model\RpcServiceEntity;
use ZF\Hal\Entity;
use ZF\Hal\Link\Link;
use ZF\Hal\Link\LinkCollection;
use ZF\Hal\Plugin\Hal;
use ZF\Hal\View\HalJsonModel;
use ZFTest\Apigility\Admin\RouteAssetsTrait;
use ZFTest\Apigility\Admin\TestAsset\Closure as MockClosure;

class InjectModuleResourceLinksListenerTest extends TestCase
{
    use RouteAssetsTrait;

    public function setUp()
    {
        $this->event           = $this->prophesize(MvcEvent::class);
        $this->routeMatch      = $this->prophesize($this->getRouteMatchClass());
        $this->events          = $this->prophesize(EventManagerInterface::class);
        $this->hal             = $this->prophesize(Hal::class);
        $this->result          = $this->prophesize(HalJsonModel::class);
        $this->urlHelper       = $this->prophesize(MockClosure::class);
        $this->serverUrlHelper = $this->prophesize(MockClosure::class);

        $this->helpers         = $this->prophesize(ContainerInterface::class);
        $this->listener        = new InjectModuleResourceLinksListener($this->helpers->reveal());
    }

    public function initRequiredConditions($listener)
    {
        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->event->getResult()->will([$this->result, 'reveal']);

        $this->helpers->get('hal')->will([$this->hal, 'reveal']);
        $this->helpers->get('Url')->willReturn(function (...$args) {
            $helper = $this->urlHelper->reveal();
            return $helper->call(...$args);
        });
        $this->helpers->get('ServerUrl')->willReturn(function (...$args) {
            $helper = $this->serverUrlHelper->reveal();
            return $helper->call(...$args);
        });

        $this->events
            ->attach(
                [
                    'renderCollection',
                    'renderEntity',
                    'renderCollection.Entity'
                ],
                [$listener, 'onHalRenderEvents']
            )
            ->shouldBeCalled();
        $this->hal->getEventManager()->will([$this->events, 'reveal']);
    }

    public function testListenerDoesNothingIfEventHasNoRouteMatch()
    {
        $listener = $this->listener;

        $this->event->getRouteMatch()->willReturn(null)->shouldBeCalled();
        $this->event->getResult()->shouldNotBeCalled();
        $this->helpers->get('hal')->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
        $this->assertAttributeEmpty('urlHelper', $listener);
    }

    public function testListenerDoesNothingIfResultIsNotAHalJsonModel()
    {
        $listener = $this->listener;

        $this->event->getRouteMatch()->will([$this->routeMatch, 'reveal'])->shouldBeCalled();
        $this->event->getResult()->willReturn(new stdClass());
        $this->helpers->get('hal')->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
        $this->assertAttributeEmpty('urlHelper', $listener);
    }

    public function testRegistersAlternateUrlHelperAndAttachesHalPluginListenersIfHalJsonModelDetected()
    {
        $listener = $this->listener;
        $this->initRequiredConditions($listener);

        $this->result->isEntity()->willReturn(false)->shouldBeCalled();
        $this->result->isCollection()->willReturn(false)->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
        $this->assertAttributeInstanceOf(Closure::class, 'urlHelper', $listener);
    }

    public function testInjectsModuleEntityWithModuleResourceRelationalLinksAndAttachesRenderEntityListener()
    {
        $moduleData = [
            'name'          => 'FooConf',
            'authorization' => [],
            'rest'          => ['ignored'],
            'rpc'           => ['ignored'],
        ];
        $listener   = $this->listener;
        $module     = $this->prophesize(ModuleEntity::class);
        $links      = $this->prophesize(LinkCollection::class);
        $payload    = new Entity($module->reveal(), 'FooConf');
        $payload->setLinks($links->reveal());
        $this->initRequiredConditions($listener);

        $this->result->isEntity()->willReturn(true)->shouldBeCalled();
        $this->result->getPayload()->willReturn($payload);

        $module->getArrayCopy()->willReturn($moduleData);
        $module->getNamespace()->willReturn('FooConf');
        $module->isVendor()->willReturn(false);

        $this->result
            ->setPayload(Argument::that(function ($entity) use ($moduleData, $links) {
                if (! $entity instanceof Entity) {
                    return false;
                }

                if (! $links->reveal() === $entity->getLinks()) {
                    return false;
                }

                $moduleEntity = $entity->getEntity();
                if (! $moduleEntity instanceof ModuleEntity) {
                    return false;
                }

                if ($moduleData['name'] !== $moduleEntity->getNamespace()) {
                    return false;
                }

                $data = $moduleEntity->getArrayCopy();
                if (! empty($data['rest'])) {
                    return false;
                }
                if (! empty($data['rpc'])) {
                    return false;
                }
                if (isset($data['authorization'])) {
                    return false;
                }

                return true;
            }))
            ->shouldBeCalled();

        $this->urlHelper
            ->call(
                'zf-apigility/api/module/authorization',
                [ 'name' => 'FooConf' ],
                [],
                false
            )
            ->willReturn('/zf-apigility/api/module/authorization');
        $this->serverUrlHelper
            ->call('/zf-apigility/api/module/authorization')
            ->willReturn('http://localhost/zf-apigility/api/module/authorization');

        $this->urlHelper
            ->call(
                'zf-apigility/api/module/rest-service',
                [ 'name' => 'FooConf' ],
                [],
                false
            )
            ->willReturn('/zf-apigility/api/module/rest-service');
        $this->serverUrlHelper
            ->call('/zf-apigility/api/module/rest-service')
            ->willReturn('http://localhost/zf-apigility/api/module/rest-service');

        $this->urlHelper
            ->call(
                'zf-apigility/api/module/rpc-service',
                [ 'name' => 'FooConf' ],
                [],
                false
            )
            ->willReturn('/zf-apigility/api/module/rpc-service');
        $this->serverUrlHelper
            ->call('/zf-apigility/api/module/rpc-service')
            ->willReturn('http://localhost/zf-apigility/api/module/rpc-service');

        $links->add(Argument::type(Link::class))->shouldBeCalledTimes(3);

        $this->events
            ->attach(
                'renderEntity',
                [$listener, 'onRenderEntity'],
                10
            )
            ->shouldBeCalled();

        $this->result->isCollection()->willReturn(false)->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function serviceEntities()
    {
        return [
            RestServiceEntity::class => [RestServiceEntity::class],
            RpcServiceEntity::class  => [RpcServiceEntity::class],
        ];
    }

    /**
     * @dataProvider serviceEntities
     */
    public function testUpdatesServiceEntityWithNormalizedControllerNameAndAttachesRenderEntityListener($entityType)
    {
        $listener = $this->listener;
        $links    = $this->prophesize(LinkCollection::class);

        $serviceEntity = new $entityType();
        $serviceEntity->exchangeArray([
            'controller_service_name'  => 'Version\V1\Rest\Foo',
        ]);

        $payload  = new Entity($serviceEntity, 'Version\V1\Rest\Foo');
        $payload->setLinks($links->reveal());
        $this->initRequiredConditions($listener);

        $this->result->isEntity()->willReturn(true)->shouldBeCalled();
        $this->result->getPayload()->willReturn($payload);

        $links->has('self')
            ->willReturn(true)
            ->shouldBeCalled();
        $links->remove('self')->shouldBeCalled();

        $this->result
            ->setPayload(Argument::that(function ($entity) use ($entityType, $serviceEntity, $links) {
                if (! $entity instanceof Entity) {
                    return false;
                }

                if ($entity->getId() !== 'Version-V1-Rest-Foo') {
                    return false;
                }

                if (! $links->reveal() === $entity->getLinks()) {
                    return false;
                }

                $foundEntity = $entity->getEntity();
                if (! $foundEntity instanceof $entityType) {
                    return false;
                }

                if ($foundEntity->controllerServiceName !== 'Version-V1-Rest-Foo') {
                    return false;
                }

                return true;
            }))
            ->shouldBeCalled();

        $this->events
            ->attach(
                'renderEntity',
                [$listener, 'onRenderEntity'],
                10
            )
            ->shouldBeCalled();

        $this->result->isCollection()->willReturn(false)->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testUpdatesInputFilterEntityWithNormalizedInputFilterNameAndAttachesRenderEntityListener()
    {
        $listener = $this->listener;
        $links    = $this->prophesize(LinkCollection::class);

        $serviceEntity = new InputFilterEntity();
        $serviceEntity->exchangeArray([
            'input_filter_name'  => 'Version\V1\Rest\Foo\InputFilter',
        ]);

        $payload  = new Entity($serviceEntity, 'Version\V1\Rest\Foo\InputFilter');
        $payload->setLinks($links->reveal());
        $this->initRequiredConditions($listener);

        $this->result->isEntity()->willReturn(true)->shouldBeCalled();
        $this->result->getPayload()->willReturn($payload);

        $links->has('self')
            ->willReturn(true)
            ->shouldBeCalled();
        $links->remove('self')->shouldBeCalled();

        $this->result
            ->setPayload(Argument::that(function ($entity) use ($serviceEntity, $links) {
                if (! $entity instanceof Entity) {
                    return false;
                }

                if ($entity->getId() !== 'Version-V1-Rest-Foo-InputFilter') {
                    return false;
                }

                if (! $links->reveal() === $entity->getLinks()) {
                    return false;
                }

                $foundEntity = $entity->getEntity();
                if (! $foundEntity instanceof InputFilterEntity) {
                    return false;
                }

                if ($foundEntity['input_filter_name'] !== 'Version-V1-Rest-Foo-InputFilter') {
                    return false;
                }

                return true;
            }))
            ->shouldBeCalled();

        $this->events
            ->attach(
                'renderEntity',
                [$listener, 'onRenderEntity'],
                10
            )
            ->shouldBeCalled();

        $this->result->isCollection()->willReturn(false)->shouldNotBeCalled();

        $this->assertNull($listener($this->event->reveal()));
    }

    public function testMemoizesRouteMatchAndAttachesRenderCollectionEntityListener()
    {
        $listener = $this->listener;
        $this->initRequiredConditions($listener);

        $this->result->isEntity()->willReturn(false)->shouldBeCalled();
        $this->result->isCollection()->willReturn(true)->shouldBeCalled();

        $this->events
            ->attach('renderCollection.entity', [$listener, 'onRenderCollectionEntity'], 10)
            ->shouldBeCalled();

        $this->assertNull($listener($this->event->reveal()));
        $this->assertAttributeSame($this->routeMatch->reveal(), 'routeMatch', $listener);
    }

    public function testOnHalRenderEventsExitsEarlyIfNoRouteMatchPresentInListener()
    {
        $event = $this->prophesize(EventInterface::class)->reveal();
        $this->assertNull($this->listener->onHalRenderEvents($event));
    }

    public function testOnHalRenderEventsExitsEarlyIfRouteMatchDoesNotContainControllerServiceName()
    {
        $event = $this->prophesize(EventInterface::class)->reveal();

        $r = new ReflectionProperty($this->listener, 'routeMatch');
        $r->setAccessible(true);
        $r->setValue($this->listener, $this->routeMatch->reveal());

        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn(null)
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', Argument::any())
            ->shouldNotBeCalled();

        $this->assertNull($this->listener->onHalRenderEvents($event));
    }

    public function testOnHalRenderEventsUpdatesControllerServiceNameInRouteMatch()
    {
        $event = $this->prophesize(EventInterface::class)->reveal();

        $r = new ReflectionProperty($this->listener, 'routeMatch');
        $r->setAccessible(true);
        $r->setValue($this->listener, $this->routeMatch->reveal());

        $this->routeMatch
            ->getParam('controller_service_name')
            ->willReturn('Foo\Bar\BazController')
            ->shouldBeCalled();
        $this->routeMatch
            ->setParam('controller_service_name', 'Foo-Bar-BazController')
            ->shouldBeCalled();

        $this->assertNull($this->listener->onHalRenderEvents($event));
    }
}
