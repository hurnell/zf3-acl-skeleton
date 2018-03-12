<?php

/**
 * Class UserManagerFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use AclUser\Service\UserManager;

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
class UserManagerFactory
{

    /**
     * Create/instantiate UserManager object 
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return UserManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $mailViewMessage = $container->get('mailViewMessage');
        $languageManager = $container->get('languageManager');
        return new UserManager($entityManager, $mailViewMessage, $languageManager);
    }

}
