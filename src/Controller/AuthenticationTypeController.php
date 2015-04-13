<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\Controller;

use ZF\ContentNegotiation\ViewModel;
use ZF\ApiProblem\ApiProblem;
use ZF\ApiProblem\ApiProblemResponse;
use ZF\MvcAuth\Authentication\DefaultAuthenticationListener as AuthListener;
use ZF\Apigility\Admin\Model\AuthenticationModel as AuthModel;

class AuthenticationTypeController extends AbstractAuthenticationController
{
    protected $model;

    public function __construct(AuthModel $model, AuthListener $authListener)
    {
        $this->model        = $model;
        $this->authListener = $authListener;
    }

    /**
     * Get the authentication type list
     * Since Apigility 1.1
     *
     */
    public function authTypeAction()
    {
        $request = $this->getRequest();
        $version = $this->getVersion($request);

        switch ($version) {
            case 1:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'This API is supported starting from version 2')
                );
            case 2:
                if ($request->getMethod() !== $request::METHOD_GET) {
                    $response = new ApiProblemResponse(
                        new ApiProblem(405, 'Only the method GET is allowed for this URI')
                    );
                    $response->getHeaders()->addHeaderLine('Allow', 'GET');
                    return $response;
                }

                return $this->createAdapterCollection();
            default:
                return new ApiProblemResponse(
                    new ApiProblem(406, 'The API version specified is not supported')
                );
        }
    }

    /**
     * Create a collection of adapters.
     *
     * @return ViewModel
     */
    private function createAdapterCollection()
    {
        if (! $this->model->fetchAllAuthenticationAdapter()) {
            // Check for old authentication configuration
            $adapters = array();
            if ($this->model->fetch()) {
                // Create a new authentication adapter for each API/version
                $adapters = array($this->model->transformAuthPerApis());
            }
            return $this->createViewModel($adapters);
        }

        $adapters = $this->authListener->getAuthenticationTypes();
        if ($adapters && isset($adapters['adapters'])) {
            $adapters = array_keys($adapters['adapters']);
        }
        return $this->createViewModel($adapters);
    }

    /**
     * Create a view model with the given adapters to indicate authentication types available.
     *
     * @param array $adapters
     * @return ViewModel
     */
    private function createViewModel($adapters)
    {
        $model = new ViewModel(array(
            'auth-types' => $adapters
        ));
        $model->setTerminal(true);
        return $model;
    }
}
