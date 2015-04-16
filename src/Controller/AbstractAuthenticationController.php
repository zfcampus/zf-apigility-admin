<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Http\Request;

abstract class AbstractAuthenticationController extends AbstractActionController
{
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
     * Get the API version from the Accept header
     *
     * @param  Request $request
     * @return integer
     */
    protected function getVersion(Request $request)
    {
        $accept = $request->getHeader('Accept', false);

        if (! $accept) {
            return 1;
        }

        if (preg_match('/application\/vnd\.apigility\.v(\d+)\+json/', $accept->getFieldValue(), $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }
}
