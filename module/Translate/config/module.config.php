<?php

/**
 * Get/add to application's configuration settings
 *
 * @package     Translate
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate;

use Zend\Router\Http\Segment;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'translate' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/translate/:action/:language/:type[[/:idx]/:index]',
                    'constraints' => [
                        'language' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'type' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'translate',
                        'action' => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\TranslationController::class => Controller\Factory\TranslationControllerFactory::class,
        ],
        'aliases' => [
            'translate' => Controller\TranslationController::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            'translateContollerPlugin' => Controller\Factory\Plugin\TranslatorControllerPluginFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            Service\TranslationManager::class => Service\Factory\TranslationManagerFactory::class,
            Service\TranslationSaver::class => Service\Factory\TranslationSaverFactory::class,
        ],
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'controllers' => ['translate'],
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\FlagNavigation::class => View\Helper\Factory\FlagNavigationFactory::class,
            View\Helper\SessionTime::class => View\Helper\Factory\SessionTimeFactory::class,
        ],
        'aliases' => [
            'flagNavigation' => View\Helper\FlagNavigation::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'language_locales' => [/* list of all possible languages */
        'en_GB' => 'English',
        'nl_NL' => 'nederlands',
        'es_ES' => 'Español',
        'fr_FR' => 'français',
        'de_DE' => 'Deutsche',
        'it_IT' => 'italiano',
        'el_GR' => 'ελληνικά',
        'nn_NO' => 'norsk',
        'pl_PL' => 'Polskie',
        'pt_PT' => 'português',
        'ru_RU' => 'русский',
        'sv_SE' => 'svenska',
        'fi_FI' => 'Suomi',
    ],
];

