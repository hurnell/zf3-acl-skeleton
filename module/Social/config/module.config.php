<?php

/**
 * Get/add to application's configuration settings
 *
 * @package     Social
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social;

use Zend\Router\Http\Segment;

return [
    'router' => [
        'routes' => [
            'social' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/social/:action/:provider',
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'social',
                        'action' => 'login-start',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\SocialController::class => Controller\Factory\SocialControllerFactory::class,
        ],
        'aliases' => [
            'social' => Controller\SocialController::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\SocialManager::class => Service\Factory\SocialManagerFactory::class,
            Service\SocialAuthManager::class => Service\Factory\SocialAuthManagerFactory::class,
            Options\ModuleOptions::class => Options\Factory\ModuleOptionsFactory::class,
        ],
        'aliases' => [
        ],
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'controllers' => ['social'],
            ],
        ],
    ],
    'session_containers' => [
        'social_saved_state'
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\SocialProvider::class => View\Helper\Factory\SocialProviderFactory::class,
        ],
        'aliases' => [
            'socialProvider' => View\Helper\SocialProvider::class,
        ],
    ],
];

