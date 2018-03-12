<?php

/**
 * Class SocialAuthManager
 *
 * @package     Social\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Service;

use Zend\Authentication\Result;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use AclUser\Entity\User;
use AclUser\Entity\UserRoleMap;
use AclUser\Entity\Role;
use AclUser\Service\UserManager;
use Zend\Math\Rand;

/**
 * The SocialAuthManager service is responsible for user's login and registration through social OAuth
 *
 * @package     Social\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialAuthManager
{

    /**
     * Entity manager.
     * 
     * @var Doctrine\ORM\EntityManager 
     */
    private $entityManager;

    /**
     * The service used to authenticate users
     * 
     * @var AuthenticationService 
     */
    private $authService;

    /**
     * Name of the user entity class
     * 
     * @var string
     */
    private $userClass;

    /**
     * AclUser UserManager handles logic for User
     * 
     * @var UserManager 
     */
    private $userManager;

    /**
     * Instantiate SocialAuthManager object and inject services
     * 
     * @param EntityManager $entityManager
     * @param string $userClass
     * @param AuthenticationService $authService
     * @param UserManager $userManager
     */
    public function __construct(EntityManager $entityManager, $userClass, AuthenticationService $authService, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userClass = $userClass;
        $this->authService = $authService;
        $this->userManager = $userManager;
    }

    /**
     * Complete social sign-in when the provider returns the user profile array
     * 
     * @param array $clientRequestResult the authenticate user
     * @return Result
     */
    public function completeSocialLogin($clientRequestResult)
    {

        $email = $clientRequestResult['email'];
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneBy(['email' => $email]);
        if (null != $user) {
            if ($user->getStatus() !== User::STATUS_RETIRED) {
                return $this->signUserIn($user);
            } else {
                return new Result(
                        Result::SUCCESS, $user, ['error' => 'The account for this e-mail address has been suspended.', 'email' => $email]);
            }
        }
        return new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['error' => 'This e-mail address does not have an account.', 'email' => $email]);
    }

    /**
     * Complete social registration when the provider returns the user profile array
     * 
     * @param type $clientRequestResult
     * @return Result
     */
    public function completeSocialRegistration($clientRequestResult)
    {
        $email = $clientRequestResult['email'];
        // check to see if this e-mail address has account and fail 
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneBy(['email' => $clientRequestResult['email']]);
        if (null == $user) {
            return $this->createNewUser($clientRequestResult);
        }
        return new Result(
                Result::FAILURE, null, ['error' => 'This e-mail address already has an account.', 'email' => $email]);
    }

    /**
     * Complete sign in logic for this User
     * 
     * @param User $user
     * @return Result
     */
    protected function signUserIn($user)
    {
        if ($this->authService->hasIdentity()) {
            $this->authService->clearIdentity();
        }
        $this->authService->getStorage()->write($user->getId());
        // die($this->authService->getIdentity());
        return new Result(
                Result::SUCCESS, $user, ['success' => 'You have been successfully logged in.']);
    }

    /**
     * Create new user when someone registers with their social media account
     * 
     * @param array $clientRequestResult
     * @return Result
     */
    protected function createNewUser($clientRequestResult)
    {

        $user = new $this->userClass();
        $user->setEmail($clientRequestResult['email']);
        $user->setPhoto(false);
        $user->setFullName($clientRequestResult['name']);
        $user->setPassword($clientRequestResult['provider']);
        $user->setStatus(User::STATUS_RETIRED);
        $user->setDateCreated(new \DateTime());

        $user->setPwdResetToken(Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true));
        // Add the entity to the entity manager.
        $user->setPwdResetTokenDate(new \DateTime());
        $this->entityManager->persist($user);
        // Apply changes to database.
        $this->entityManager->flush();

        $this->grantUserBasicRights($user);
        //return $this->signUserIn($user);
        $this->userManager->sendConfirmNewAccountEmail($user);
        return new Result(Result::SUCCESS, $user, ['success' => 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.']);
    }

    /**
     * Grant newly created user initial rights
     * 
     * @param User $user
     */
    protected function grantUserBasicRights(User $user)
    {
        $userRoleMap = new UserRoleMap();
        $userRoleMap->setUser($user);
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => 'basic']);
        $userRoleMap->setRole($role);
        $this->entityManager->persist($userRoleMap);
        // Apply changes to database.
        $this->entityManager->flush();
    }

    /**
     * Complete social sign in or registration when and/or is enabled on login page
     * 
     * @param array $clientRequestResult
     * @return Result
     */
    public function completeSocialLoginOrRegistration($clientRequestResult)
    {
        // check to see if this e-mail address has account and fail 
        $user = $this->entityManager->getRepository($this->userClass)
                ->findOneBy(['email' => $clientRequestResult['email']]);
        if (null == $user) {
            return $this->createNewUser($clientRequestResult);
        } else {
            return $this->signUserIn($user);
        }
    }

}
