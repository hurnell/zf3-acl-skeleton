<?php

/**
 * Class ManageUsersManagerFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use AclUser\Service\ManageUsersManager;

/**
 * This is the factory class for UserManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 * 
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ManageUsersManagerFactory {

    /**
     * Create/instantiate ManageUsersManager object with injected dependencies
     * 
     * @param ContainerInterface $container
     * @param type $requestedName
     * @param array $options
     * @return ManageUsersManager
     */
    public function __invoke(ContainerInterface $container, $requestedName,
            array $options = null) {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');

        $mailViewMessage = $container->get('mailViewMessage');
        $languageManager = $container->get('languageManager');
        return new ManageUsersManager($entityManager, $mailViewMessage, $languageManager);
    }

}
