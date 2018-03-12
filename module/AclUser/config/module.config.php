<?php

/**
 * Define configuration settings for AclUser module
 *
 * @package     AclUser
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\Authentication\AuthenticationService;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;

return [
    'router' => [
        'routes' => [
            'identity' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '#',
                ],
            ],
            'manage-users' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/manage-users/:action/:id',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'manage-users',
                        'action' => 'index',
                    ],
                ],
            ],
            'auth-id' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/user-auth/:action/:id',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'auth',
                        'action' => 'ajax-generate-new-password',
                    ],
                ],
            ],
            'user-id' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/user/:action/:id',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'user',
                        'action' => 'serve-user-photo',
                    ],
                ],
            ],
            'send-token' => [
                'type' => Segment::class,
                'options' => [
                    'route' => '/:locale/user-auth/:action/:token',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'token' => '[a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'locale' => 'en_GB',
                        'controller' => 'user-auth',
                        'action' => 'reset-password',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
            Controller\ManageUsersController::class => Controller\Factory\ManageUsersControllerFactory::class,
        ],
        'aliases' => [
            'user-auth' => Controller\AuthController::class,
            'user' => Controller\UserController::class,
            'manage-users' => Controller\ManageUsersController::class,
        ], //*/
    ],
    'controller_plugins' => [
        'invokables' => [
        ],
        'factories' => [
            'redirectMessage' => Controller\Factory\Plugin\RedirectMessageControllerPluginFactory::class,
            'aclControllerPlugin' => Controller\Factory\Plugin\UserIsAllowedControllerPluginFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            AuthenticationService::class => Service\Factory\AuthenticationServiceFactory::class,
            Service\AuthAdapter::class => Service\Factory\AuthAdapterFactory::class,
            Service\AuthManager::class => Service\Factory\AuthManagerFactory::class,
            Service\UserManager::class => Service\Factory\UserManagerFactory::class,
            Service\ManageUsersManager::class => Service\Factory\ManageUsersManagerFactory::class,
            Mail\MailMessage::class => Service\Factory\ViewScriptMailMessageFactory::class,
        ],
        'aliases' => [
            'mailViewMessage' => Mail\MailMessage::class,
        ]
    ],
    'translator' => [
        'locale' => 'en_GB',
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
                'controllers' => ['user-auth', 'user']
            ],
        ],
    ],
    'view_helpers' => [
        'factories' => [
            'aclViewHelper' => View\Helper\Factory\UserIsAllowedViewHelperFactory::class
        ]
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
        'template_map' => [
            'email/layout' => __DIR__ . '/../view/layout/email-layout.phtml',
        ],
    ],
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
];

