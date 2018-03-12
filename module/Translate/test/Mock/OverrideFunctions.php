<?php

/**
 * file OverrideFunction
 * Override Native Php functions 
 *
 * @package     TranslateTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace Translate\Service;

if (!array_key_exists('use_override', $GLOBALS)) {
    $GLOBALS['use_override'] = false;
}

/**
 * Override realpath() in Translate\Service namespace for testing
 *
 * @param string $path     the file path
 *
 * @return string
 */
function realpath($path)
{
    if (true === $GLOBALS['use_override']) {
        return $path;
    }
    return \realpath($path);
    //echo 'realpath' . "\n";
}

/**
 * Override file_exists() in Translate\Service namespace for testing
 *
 * @param string $path     the file path *
 * @return boolean
 */
function file_exists($path)
{
    // echo 'file_exists' . "\n";
    if (true === $GLOBALS['use_override']) {
        switch ($path) {
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/enabled/en_GB.png'):
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/enabled/en_EN.png'):
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/av_AV.png'):
                return true;
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/enabled/av_AV.png'):
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/enabled/mi_MI.png'):
            case str_replace('/', DIRECTORY_SEPARATOR, './public/img/flags/mi_MI.png'):
                return false;
            default:
                return \file_exists($path);
        }
    }
}
namespace Application\Log;
/**
 * Override php_sapi_name() in Application\Log namespace for testing
 * 
 * @return string
 */
function php_sapi_name()
{
    if (true === $GLOBALS['use_override']) {
        return 'anything';
    }
    return \php_sapi_name();
}
namespace Application;

/**
 * Override php_sapi_name() in Application\Service namespace for testing
 * 
 * @return string
 */
function php_sapi_name()
{
    if (true === $GLOBALS['use_override']) {
        return 'anything';
    }
    return \php_sapi_name();
}


/**
 * Override setcookie() in Application namespace for testing
 * 
 * @param string $name
 * @param string $value
 * @param int $expires
 * @param string $path
 * @param string $domain
 * @param boolean $secure
 * @param boolean $httponly
 * @return boolean
 */
function setcookie($name, $value = '', $expires = 0, $path = '', $domain = '', $secure = false, $httponly = false)
{
    if (true === $GLOBALS['use_override']) {
        return true;
    }
    return \setcookie($name, $value, $expires, $path, $domain, $secure, $httponly);
}
