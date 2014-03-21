<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\Exception;

class HttpMethodArrayValidator extends AbstractValidator
{
    const HTTP_METHOD_ARRAY = 'httpMethodArray';

    /**
     * @var array
     */
    protected $validHttpMethods = array(
        'OPTIONS',
        'GET',
        'POST',
        'PATCH',
        'PUT',
        'DELETE'
    );

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::HTTP_METHOD_ARRAY => "'%value%' is not http method"
    );

    /**
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        foreach ($value as $httpMethod) {
            if (!in_array($httpMethod, $this->validHttpMethods)) {
                $this->error(self::HTTP_METHOD_ARRAY, $httpMethod);
                return false;
            }
        }
        return true;
    }
}
