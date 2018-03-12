<?php

/**
 * Class UserControllerFactory
 *
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use AclUser\Controller\UserController;
use AclUser\Service\UserManager;

/**
 * This is the factory for UserControllerFactory. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 * 
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class UserControllerFactory implements FactoryInterface
{

    /**
     * Create/instantiate UserController object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param null|array $options
     * @return UserController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $userManager = $container->get(UserManager::class);

        // Instantiate the controller and inject dependencies
        return new UserController($entityManager, $userManager);
    }

}
