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
require_once './module/AclUser/test/Mocked/TestAccessControlList.php';
require_once './module/AclUser/test/Mocked/TestAccessControlListFactory.php';
return [
    'service_manager' => [
        'factories' => [
            AclUserTest\Mocked\TestAccessControlList::class => AclUserTest\Mocked\TestAccessControlListFactory::class,
            AclUser\Permissions\Acl\AccessControlList::class => AclUser\Permissions\Acl\Factory\AccessControlListFactory::class,
        ],
        'aliases' => [
            'AccessControlList' => AclUserTest\Mocked\TestAccessControlList::class,
            'unmockedAccessControlList' => AclUser\Permissions\Acl\AccessControlList::class,
        ]
    ],
];
