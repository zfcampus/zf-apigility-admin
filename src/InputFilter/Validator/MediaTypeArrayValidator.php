<?php

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\Exception;

class MediaTypeArrayValidator extends AbstractValidator
{
    const MEDIA_TYPE_ARRAY = 'mediaTypeArray';

    protected $messageTemplates = array(
        self::MEDIA_TYPE_ARRAY => "'%value%' is not a correctly formatted media type"
    );

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @return bool
     * @throws Exception\RuntimeException If validation of $value is impossible
     */
    public function isValid($value)
    {
        foreach ($value as $mediaType) {
            // preg_match('#a-zA-Z0-9!\#$%^&\*_-\+{}\|\'.`~]+/[a-zA-Z0-9!\#$%^&\*_-\+{}\|\'.`~]+#', $mediaType)
            if (strpos($mediaType, '/') === false) {
                $this->error(self::MEDIA_TYPE_ARRAY, $mediaType);
                return false;
            }
        }
        return true;
    }
}
