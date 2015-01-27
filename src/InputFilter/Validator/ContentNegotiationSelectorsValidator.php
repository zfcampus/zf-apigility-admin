<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\AbstractValidator as ZfAbstractValidator;

class ContentNegotiationSelectorsValidator extends ZfAbstractValidator
{
    const INVALID_VALUE       = 'invalidValue';
    const CLASS_NOT_FOUND     = 'classNotFound';
    const INVALID_VIEW_MODEL  = 'invalidViewModel';
    const INVALID_MEDIA_TYPES = 'invalidMediaTypes';
    const INVALID_MEDIA_TYPE  = 'invalidMediaType';

    protected $messageTemplates = array(
        self::INVALID_VALUE       => 'Value must be an array; received %value%',
        self::CLASS_NOT_FOUND     => 'Class name (%value%) does not exist',
        self::INVALID_VIEW_MODEL  =>
            'Class name (%value%) is invalid; must be a valid Zend\View\Model\ModelInterface instance',
        self::INVALID_MEDIA_TYPES => 'Values for the media-types must be provided as an indexed array',
        self::INVALID_MEDIA_TYPE  => 'Invalid media type (%value%) provided',
    );

    /**
     * Test if a set of selectors is valid
     *
     * @param array $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        if (! is_array($value)) {
            $this->error(
                self::INVALID_VALUE,
                (is_object($value) ? get_class($value) : gettype($value))
            );
            return false;
        }

        foreach ($value as $className => $mediaTypes) {
            if (! class_exists($className)) {
                $isValid = false;
                $this->error(self::CLASS_NOT_FOUND, $className);
                continue;
            }

            $interfaces = class_implements($className);
            if (false === $interfaces || ! in_array('Zend\View\Model\ModelInterface', $interfaces)) {
                $isValid = false;
                $this->error(self::INVALID_VIEW_MODEL, $className);
                continue;
            }

            if (! is_array($mediaTypes)) {
                $isValid = false;
                $this->error(self::INVALID_MEDIA_TYPES);
                continue;
            }

            foreach ($mediaTypes as $mediaType) {
                if (strpos($mediaType, '/') === false) {
                    $isValid = false;
                    $this->error(self::INVALID_MEDIA_TYPE, $mediaType);
                }
            }
        }

        return $isValid;
    }
}
