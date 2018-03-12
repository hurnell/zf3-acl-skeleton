<?php

/**
 * Class AuthAdapter
 *
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service;

use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use AclUser\Entity\User;
use Doctrine\ORM\EntityManager;

/**
 * Adapter used for authenticating user. It takes login and password on input
 * and checks the database if there is a user with such login (email) and password.
 * If such user exists, the service returns its identity (email). The identity
 * is saved to session and can be retrieved later with Identity view helper provided
 * by ZF3.
 * 
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthAdapter implements AdapterInterface
{

    /**
     * User email.
     * 
     * @var string 
     */
    private $email;

    /**
     * User userId.
     * 
     * @var integer 
     */
    private $userId;

    /**
     * Password
     * 
     * @var string 
     */
    private $password;

    /**
     * Entity manager.
     * 
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;

    /**
     * Constructor
     * 
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Sets user email.   
     * 
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Sets password. 
     * 
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = (string) $password;
    }

    /**
     * Performs an authentication attempt.
     */
    public function authenticate()
    {
        // Check the database if there is a user with such email.
        $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $this->email]);
        $messages = ['email' => $this->email];
        // If there is no such user, return 'Identity Not Found' status.
        if ($user == null) {
            $messages['error'] = 'This e-mail address does not have an account.';
            return new Result(
                    Result::FAILURE_IDENTITY_NOT_FOUND, null, $messages);
        }
        // If the user with such email exists, we need to check if it is active or retired.
        // Do not allow retired users to log in.
        if ($user->getStatus() == User::STATUS_RETIRED) {
            $messages['error'] = 'The account for this e-mail address has been suspended.';
            return new Result(
                    Result::FAILURE, null, $messages);
        }

        // Now we need to calculate hash based on user-entered password and compare
        // it with the password hash stored in database.
        $bcrypt = new Bcrypt();
        $passwordHash = $user->getPassword();

        $this->userId = $user->getId();

        if ($bcrypt->verify($this->password, $passwordHash)) {
            // Great! The password hash matches. Return user identity (email) to be
            // saved in session for later use.
            $messages['success'] = 'You have been successfully logged in.';
            unset($messages['email']);
            return new Result(
                    Result::SUCCESS, $this->userId, $messages);
        }

        // If password check didn't pass return 'Invalid Credential' failure status.
        $messages['error'] = 'Incorrect password for this e-mail address.';
        return new Result(
                Result::FAILURE_CREDENTIAL_INVALID, null, $messages);
    }

    /**
     * Create and assign messages and success status to Zend\Authentication\Result; 
     * 
     * @return Result
     */
    public function adapterCompleteSocialLogin()
    {
        $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['email' => $this->email]);
        if ($user == null) {
            // If there is no such user, return 'Identity Not Found' status.
            $result = new Result(
                    Result::FAILURE_IDENTITY_NOT_FOUND, null, ['error' => 'This e-mail address does not have an account.']);
        } else if ($user->getStatus() != User::STATUS_ACTIVE) {
            $result = new Result(
                    Result::FAILURE, null, ['error' => 'The account for this e-mail address has been suspended.']);
        } else {
            $this->userId = $user->getId();
            $result = new Result(
                    Result::SUCCESS, $this->userId, ['success' => 'You have been successfully logged in.']);
        }
        return $result;
    }

}
