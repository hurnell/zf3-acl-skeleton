<?php

/**
 * Config file where basic modules used in the application are enumerated for 
 * creation.
 * 
 * @package Config
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
$modules = [
    'Zend\Cache',
    'Zend\Paginator',
    'Zend\ServiceManager\Di',
    'Zend\Navigation',
    'Zend\Mail',
    'Zend\Mvc\Plugin\FilePrg',
    'Zend\Mvc\Plugin\FlashMessenger',
    'Zend\Mvc\Plugin\Identity',
    'Zend\Mvc\Plugin\Prg',
    'Zend\Session',
    'Zend\Mvc\I18n',
    'Zend\Mvc\Console',
    'Zend\Router',
    'Zend\Log',
    'Zend\Form',
    'Zend\Hydrator',
    'Zend\InputFilter',
    'Zend\Filter',
    'Zend\I18n',
    'Zend\Db',
    'Zend\Validator',
    'DoctrineModule',
    'DoctrineORMModule',
];
/* if thiis script is invoked as part of database migration or generation then
 * do not include basic application  modules */
if (array_key_exists('argv', $GLOBALS) && is_array($GLOBALS['argv']) && count($GLOBALS['argv']) > 1 && ($GLOBALS['argv']['1'] == 'migrations:migrate' || $GLOBALS['argv']['1'] == 'migrations:generate')) {
    echo 'IGNORING MODULEs' . "\n";
} else {
    $modules[] = 'Application';
    $modules[] = 'AclUser';
    $modules[] = 'Translate';
    $modules[] = 'Social';
}

return $modules;
