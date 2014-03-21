<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */

namespace ZF\Apigility\Admin\InputFilter\Validator;

use Zend\Validator\AbstractValidator as BaseAbstractValidator;

abstract class AbstractValidator extends BaseAbstractValidator
{
    protected $reservedWords = array(
        '__halt_compiler',
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'callable',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'die',
        'do',
        'echo',
        'else',
        'elseif',
        'empty',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'eval',
        'exit',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'include',
        'include_once',
        'instanceof',
        'insteadof',
        'interface',
        'isset',
        'list',
        'namespace',
        'new',
        'or',
        'print',
        'private',
        'protected',
        'public',
        'require',
        'require_once',
        'return',
        'static',
        'switch',
        'throw',
        'trait',
        'try',
        'unset',
        'use',
        'var',
        'while',
        'xor',
    );

    /**
     * Is the given string a valid "name" in PHP?
     *
     * Verify that the string is not a PHP keyword and/or will be usable as a
     * variable name, namespace, or class name.
     *
     * @param  string $word
     * @return bool
     */
    public function isValidWordInPhp($word)
    {
        if (in_array(strtolower($word), $this->reservedWords)) {
            return false;
        }

        // can't start with _
        if (! preg_match('/^[a-zA-Z\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $word)) {
            return false;
        }

        return true;
    }
}
