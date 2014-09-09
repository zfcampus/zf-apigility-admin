<?php
/**
 * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
 * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
 */
namespace ZF\Apigility\Admin;

class Utility
{
    /**
     * Recursive delete
     *
     * @param  string $dir
     * @return boolean
     */
    public static function recursiveDelete($dir)
    {
        if (false === ($dh = @opendir($dir))) {
            return false;
        }

        while (false !== ($obj = readdir($dh))) {
            if ($obj == '.' || $obj == '..') {
                continue;
            }
            if (!@unlink($dir . '/' . $obj)) {
                self::recursiveDelete($dir . '/' . $obj);
            }
        }

        closedir($dh);
        @rmdir($dir);
        return true;
    }
}
