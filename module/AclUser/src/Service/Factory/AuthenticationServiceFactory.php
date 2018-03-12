<?php

/**
 * Class AuthenticationServiceFactory
 *
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service\Factory;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\Factory\FactoryInterface;
use Zend\Session\SessionManager;
use Zend\Authentication\Storage\Session as SessionStorage;
use AclUser\Service\AuthAdapter;
use Zend\Session\Exception\ExceptionInterface as SessionException;

/**
 * The factory responsible for creating of authentication service.
 * 
 * @package     AclUser\Service\Factory
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthenticationServiceFactory implements FactoryInterface {

    /**
     * count of number of attempts
     * 
     * @var integer the number of tries 
     */
    protected $count = 0;

    /**
     * Create/instantiate AuthenticationService object with injected dependencies
     * 
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return AuthenticationService
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null) {
        return $this->getAuthService($container);
    }

    /**
     * System has a tendency to fail so if an exception in thrown the session is expired an a new on created
     *  
     * @param ContainerInterface $container
     * @return AuthenticationService
     */
    protected function getAuthService(ContainerInterface $container) {
        $this->count ++;
        $sessionManager = $container->get(SessionManager::class);
        try {
            $authStorage = new SessionStorage('Zend_Auth', 'session', $sessionManager);
            $authAdapter = $container->get(AuthAdapter::class);
            $authService = new AuthenticationService($authStorage, $authAdapter);
            // Create the service and inject dependencies into its constructor.
            return $authService;
        } catch (SessionException $exception) {
            $sessionManager->expireSessionCookie();
            if ($this->count > 3) {
                var_dump($exception->getTraceAsString());
                die(__METHOD__);
            }
            return $this->getAuthService($container);
        }
    }

}
