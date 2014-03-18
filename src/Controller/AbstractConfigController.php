<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\Configuration\ConfigResource;

abstract class AbstractConfigController extends AbstractActionController
{
    abstract public function getConfig();

    public function processAction()
    {
        $request     = $this->getRequest();
        $headers     = $request->getHeaders();
        $accept      = $this->getHeaderType($headers, 'accept');
        $contentType = $this->getHeaderType($headers, 'content-type');
        $returnTrees = 'application/json' == $accept ? false : true;

        $config = $this->getConfig();
        if (!$config instanceof ConfigResource) {
            return $config;
        }

        switch ($request->getMethod()) {
            case $request::METHOD_GET:
                return $config->fetch($returnTrees);
            case $request::METHOD_PATCH:
                $submitTrees = 'application/json' == $contentType ? false : true;
                $params      = $this->getBodyParams($submitTrees);
                $result      = $config->patch($params, $submitTrees);

                // If same accept and content-type, return the result directly
                if ($submitTrees === $returnTrees) {
                    return $result;
                }

                // If tree was submitted, but not Accepted, create dot-separated values
                if ($submitTrees && !$returnTrees) {
                    return $config->traverseArray($params);
                }

                // If tree was not submitted, but is Accepted, create nested values
                $return = array();
                foreach ($params as $key => $value) {
                    $config->createNestedKeyValuePair($return, $key, $value);
                }
                return $return;
            default:
                return new ApiProblemResponse(
                    new ApiProblem(405, 'Only the methods GET and PATCH are allowed for this URI')
                );
        }
    }

    /**
     * Set the request object manually
     *
     * Provided for testing.
     *
     * @param  Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Get the body params
     *
     * The body params plugin only knows about application/json, not our custom
     * vendor type; if using our custom vendor type, parse the content.
     *
     * @param  bool $useTrees
     * @return array
     */
    protected function getBodyParams($useTrees)
    {
        if (!$useTrees) {
            return $this->bodyParams();
        }

        return json_decode($this->getRequest()->getContent(), true);
    }

    /**
     * Get the mediatype from a given header
     *
     * @param  \Zend\Http\Headers $headers
     * @param  string $header
     * @return string
     */
    protected function getHeaderType($headers, $header)
    {
        if (!$headers->has($header)) {
            return 'application/json';
        }

        $accept = $headers->get($header);
        $value  = $accept->getFieldValue();
        $value  = explode(';', $value, 2);
        $accept = array_shift($value);
        $accept = strtolower(trim($accept));
        switch ($accept) {
            case 'application/json':
            case 'application/vnd.zfcampus.v1.config+json':
                return $accept;
            default:
                return 'application/json';
        }
    }
}
