<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\Exception;

class MediaTypeArrayValidator extends AbstractValidator
{
    const MEDIA_TYPE_ARRAY = 'mediaTypeArray';

    /**
     * @var array
     */
    protected $messageTemplates = array(
        self::MEDIA_TYPE_ARRAY => "'%value%' is not a correctly formatted media type"
    );

    /**
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        if (is_string($value)) {
            $value = (array) $value;
        }

        foreach ($value as $mediaType) {
            if (! preg_match('#^[a-z-]+/[a-z0-9*_+.-]+#i', $mediaType)) {
                $this->error(self::MEDIA_TYPE_ARRAY, $mediaType);
                return false;
            }
        }

        return true;
    }
}
