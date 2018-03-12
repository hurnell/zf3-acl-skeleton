<?php

/**
 * Class AuthManager
 *
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service;

use Zend\Authentication\Result;
use Zend\Authentication\AuthenticationService;

/**
 * The AuthManager service is responsible for user's login/logout and simple access 
 * filtering. The access filtering feature checks whether the current visitor 
 * is allowed to see the given page or not. 
 * 
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell 
 */
class AuthManager
{

    /**
     * Session manager.
     * 
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;

    /**
     * Zend authentication service that handles session management for authorised 
     * users and can be used for authentication of users
     * @var AuthenticationService 
     */
    private $authService;

    /**
     * Constructs the service.
     * 
     * @param AuthenticationService $authService
     * @param Zend\Session\SessionManager $sessionManager
     */
    public function __construct(AuthenticationService $authService, $sessionManager)
    {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
    }

    /**
     * Performs a login attempt. If $rememberMe argument is true, it forces the session
     * to last for one month (otherwise the session expires on one hour).
     * 
     * @param string $email
     * @param string $password
     * @param boolean $rememberMe
     * @return Zend\Authentication\Result object that carries message and boolean success
     * @throws \Exception
     */
    public function login($email, $password, $rememberMe = false)
    {
        // Check if user has already logged in. If so, do not allow to log in 
        // twice.
        if ($this->authService->getIdentity() != null) {
            throw new \Exception('Already logged in');
        }

        // Authenticate with login/password.
        $authAdapter = $this->authService->getAdapter();
        $authAdapter->setEmail($email);
        $authAdapter->setPassword($password);
        $result = $this->authService->authenticate();

        // If user wants to "remember him", we will make session to expire in 
        // one month. By default session expires in 1 hour (as specified in our 
        // config/global.php file).
        if ($result->getCode() == Result::SUCCESS && $rememberMe) {
            // Session cookie will expire in 1 month (30 days).
            $this->sessionManager->rememberMe(60 * 60 * 24 * 30);
        }

        return $result;
    }

    /**
     * Login user after we are sure there is one
     * 
     * @param \AclUser\Entity\User $user
     */
    public function loginUser(\AclUser\Entity\User $user)
    {
        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
        }
        $this->authService->getStorage()->write($user->getId());
    }

    /**
     * Performs user logout.
     */
    public function logout()
    {
        // Allow to log out only when user is logged in.
        if ($this->authService->getIdentity() == null) {
            throw new \Exception('The user is not logged in');
        }
        // Remove identity from session.
        $this->authService->clearIdentity();
    }

    /**
     * Validate login form and assign values to result object
     * 
     * @param AclUser\Form\LoginForm $form
     * @param array $params
     * @return Zend\Authentication\Result
     */
    public function validateLoginForm(\AclUser\Form\LoginForm $form, $params)
    {
        $form->setData($params);
        $result = new Result(
                Result::FAILURE_UNCATEGORIZED, null, ['error' => 'Form is not valid.']);

        // Validate form
        if ($form->isValid()) {
            // Get filtered and validated data
            $data = $form->getData();
            $result = $this->login($data['email'], $data['password'], $data['remember_me']);
        }
        return $result;
    }

}
