<?php

/**
 * Basic configuration file
 * 
 * @package     Config
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\RemoteAddr;
use Application\Module;

$env = defined('APPLICATION_ENV') ? APPLICATION_ENV : 'production';

return [
    'session_config' => [
        // Session cookie will expire in 1 hour.
        'cookie_lifetime' => Module::COOKIE_LIFETIME,
        'remember_me_seconds' => Module::COOKIE_LIFETIME,
        // Session data will be stored on server maximum for 30 days.
        'gc_maxlifetime' => 60 * 60 * 24 * 30,
        'use_cookies' => true,
        'use_only_cookies' => true,
        'cookie_httponly' => true,
        'cookie_secure' => $env !== 'development',
        'name' => Module::SESSION_NAME,
        'save_path' => __DIR__ . '/../../data/session/',
    ],
    // Session manager configuration.
    'session_manager' => [
        // Session validators (used for security).
        'validators' => [
            RemoteAddr::class,
        // HttpUserAgent::class,
        ]
    ],
    // Session storage configuration.
    'session_storage' => [
        'type' => SessionArrayStorage::class
    ],
    'service_manager' => [
        'factories' => [
            Translate\Mvc\I18n\TranslatorFactory::class => Translate\Mvc\I18n\TranslatorFactory::class,
            AclUser\Permissions\Acl\AccessControlList::class => AclUser\Permissions\Acl\Factory\AccessControlListFactory::class,
            Translate\Service\LanguageManager::class => Translate\Service\Factory\LanguageManagerFactory::class,
        ],
        'aliases' => [
            Zend\Mvc\I18n\Translator::class => Translate\Mvc\I18n\TranslatorFactory::class,
            'AccessControlList' => AclUser\Permissions\Acl\AccessControlList::class,
            'languageManager' => Translate\Service\LanguageManager::class,
        ]
    ],
    'navigation_helpers' => array(
        'invokables' => array(
            'menu' => Application\View\Helper\Navigation\Menu::class,
        ),
    ),
    'view_helpers' => [
        'invokables' => [
            'translate' => Translate\I18n\View\Helper\Translate::class
        ]
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../../module/Application/language',
                'pattern' => '%s.mo',
                'text_domain' => 'global',
                'controllers' => ['global']
            ],
        ],
    ],
];
