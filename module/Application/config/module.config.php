<?php

/**
 * Get/add to application's configuration settings
 *
 * @package     Application
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'default' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/:controller/:action',
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
            ],
            'entry-point' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/[:locale]',
                    'defaults' => [
                        'controller' => 'index',
                        'action' => 'entry-point',
                    ],
                ],
            ],
            'public' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/',
                    'defaults' => [
                        'controller' => 'index',
                        'action' => 'entry-point',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\IndexController::class => InvokableFactory::class,
        ],
        'aliases' => [
            'index' => Controller\IndexController::class,
        ], //*/
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            'rawResponse' => Controller\Factory\Plugin\RawResponseControllerPluginFactory::class,
        ],
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'controllers' => ['index'],
            ],
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions' => true,
        'doctype' => 'HTML5',
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
        'template_map' => [
            'layout/layout' => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404' => __DIR__ . '/../view/error/404.phtml',
            'error/index' => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
