<?php

/**
 * Get/add to application's configuration settings
 *
 * @package     Config
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */


$navReader = new Zend\Config\Reader\Xml();
$config['navigation'] = $navReader->fromFile(__DIR__ . '/../navigation/navigation.xml');

return $config;
