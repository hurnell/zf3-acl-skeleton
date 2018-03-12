<?php

/**
 * Class AuthManagerFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Authentication\AuthenticationService;
use Zend\Session\SessionManager;
use AclUser\Service\AuthManager;

/**
 * This is the factory class for AuthManager service. The purpose of the factory
 * is to instantiate the service and pass it dependencies (inject dependencies).
 * 
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthManagerFactory implements FactoryInterface {

    /**
     * Create/instantiate AuthManager object
     * 
     * @param ContainerInterface $container
     * @param type $requestedName
     * @param array $options
     * @return AuthManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        // Instantiate dependencies.
        $authenticationService = $container->get(AuthenticationService::class);
        $sessionManager = $container->get(SessionManager::class);
        // Instantiate the AuthManager service and inject dependencies to its constructor.
        $authManager = new AuthManager($authenticationService, $sessionManager);
        return $authManager;
    }

}
