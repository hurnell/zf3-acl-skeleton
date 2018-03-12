<?php

/**
 * Class AuthControllerFactory
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
use AclUser\Controller\AuthController;
use AclUser\Service\AuthManager;
use AclUser\Service\UserManager;
use Zend\Authentication\AuthenticationService;


/**
 * This is the factory for AuthControllerFactory. Its purpose is to instantiate the
 * controller and inject dependencies into it.
 * 
 * @package     AclUser\Controller\Factory
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthControllerFactory implements FactoryInterface
{

    /**
     * Create/instantiate AuthController object
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return AuthController
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $entityManager = $container->get('doctrine.entitymanager.orm_default');
        $authManager = $container->get(AuthManager::class);
        $authService = $container->get(AuthenticationService::class);
        $userManager = $container->get(UserManager::class);
        return new AuthController($entityManager, $authManager, $authService, $userManager);
    }

}
