<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Listener;

use ReflectionClass;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use ZF\ContentNegotiation\ParameterDataContainer;

class CryptFilterListener extends AbstractListenerAggregate
{
    public function attach(EventManagerInterface $events)
    {
        // Trigger between content negotiation (-625) and content validation (-650)
        $this->listeners[] = $events->attach(MvcEvent::EVENT_ROUTE, array($this, 'onRoute'), -630);
    }

    /**
     * Adjust the filter options for Crypt filter adapters
     *
     * @param MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        $request = $e->getRequest();
        if (! method_exists($request, 'isPut')
            || ! $request->isPut()
        ) {
            // Not an HTTP request, or not a PUT request; nothing to do
            return;
        }

        $matches = $e->getRouteMatch();
        if (! $matches) {
            // No route matches; nothing to do
            return;
        }

        $controller = $matches->getParam('controller', false);
        if ($controller !== 'ZF\Apigility\Admin\Controller\InputFilter') {
            // Not the InputFilter controller; nothing to do
            return;
        }

        $data = $e->getParam('ZFContentNegotiationParameterData', false);
        if (! $data) {
            // No data; nothing to do
            return;
        }

        if ($data instanceof ParameterDataContainer) {
            $data = $data->getBodyParams();
        }

        if (! isset($data['filters'])) {
            // No filters passed; nothing to do
            return;
        }

        foreach ($data['filters'] as $key => $filter) {
            if (! isset($filter['name'])) {
                continue;
            }

            $filter = $filter['name'];
            $class  = new ReflectionClass($filter);

            // If filter implements CompressionAlgorithmInterface or EncryptionAlgorithmInterface,
            // we change the filter's name to the parent, and we add the adapter param to filter's name.
            if ($class->implementsInterface('Zend\Filter\Compress\CompressionAlgorithmInterface')
                || $class->implementsInterface('Zend\Filter\Encrypt\EncryptionAlgorithmInterface')
            ) {
                $name    = substr($filter, 0, strrpos($filter, '\\'));
                $adapter = substr($filter, strrpos($filter, '\\') + 1);
                $data['filters'][$key]['name'] = $name;
                $data['filters'][$key]['options']['adapter'] = $adapter;
            }
        }

        // Inject altered data back into event
        $e->setParam('ZFContentNegotiationParameterData', $data);
        return true;
    }
}
