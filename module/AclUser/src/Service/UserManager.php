<?php

/**
 * Class UserManager
 *
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Service;

use AclUser\Entity\User;
use AclUser\Entity\UserRoleMap;
use AclUser\Entity\Role;
use Zend\Authentication\Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Math\Rand;
use AclUser\Mail\MailMessage;
use Translate\Service\LanguageManager;
use Doctrine\ORM\EntityManager;
use AclUser\Service\RotateAndResizeImageFile;

/**
 * This service is responsible for adding/editing users
 * and changing user password.
 * 
 * @package     AclUser\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class UserManager
{

    const USER_IMAGE_FOLDER = './data/media/user-images/'; // Active user.
    const PASSWORD_RESET_TOKEN_VALIDITY = 6 * 60 * 60; // 6 hours in seconds

    /**
     * Service that handles logic to actually sends an e-mail
     * 
     * @var AclUser\Mail\MailMessage 
     */
    protected $mailMessage;

    /**
     * Doctrine ORM manager/database abstraction 
     * 
     * @var EntityManager 
     */
    protected $entityManager;

    /**
     * Doctrine ORM manager/database abstraction 
     * 
     * @var EntityManager 
     */
    protected $languageManager;

    /**
     * Instantiate UserManager object and inject services
     * 
     * @param Doctrine\ORM\EntityManager $entityManager
     * @param AclUser\Mail\MailMessage $mailMessage
     * @param Translate\Service\LanguageManager $languageManager
     */
    public function __construct(EntityManager $entityManager, MailMessage $mailMessage, LanguageManager $languageManager)
    {

        $this->entityManager = $entityManager;
        $this->mailMessage = $mailMessage;
        $this->languageManager = $languageManager;
    }

    /**
     * Grand specified user the basic role
     * 
     * @param user $user
     */
    protected function giveUserBasicRole($user)
    {
        $roleEntity = $this->entityManager->getRepository(Role::class)
                ->findOneBy(['name' => 'basic']);
        $roleMap = new UserRoleMap();
        $roleMap->setUser($user);
        $roleMap->setRole($roleEntity);
        $this->entityManager->persist($roleMap);
// Apply changes to database.
        $this->entityManager->flush();
    }

    /**
     * This method updates data of an existing user.
     * 
     * @param type $user
     * @param type $data
     * @return boolean
     * @throws \Exception
     */
    public function updateUser($user, $data)
    {
// Do not allow to change user email if another user with such email already exits.
        if ($user->getEmail() != $data['email'] && $this->checkUserExists($data['email'])) {
            throw new \Exception("Another user with email address " . $data['email'] . " already exists");
        }

        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        $user->setStatus($data['status']);

// Apply changes to database.
        $this->entityManager->flush();

        return true;
    }

    /**
     * Checks whether an active user with given email address already exists in the database.  
     * 
     * @param string $email
     * @return boolean whether the user exists in the database
     */
    public function checkUserExists($email)
    {
        $user = $this->getUserByEmailAddress($email);
        return $user !== null;
    }

    /**
     * Find User by e-mail address
     * 
     * @param string $email
     * @return User
     */
    public function getUserByEmailAddress($email)
    {
        return $this->entityManager->getRepository(User::class)
                        ->findOneBy(['email' => $email]);
    }

    /**
     * Find User by database id
     * 
     * @param integer $id
     * @return User AclUser/Entity/User entity
     */
    public function getUserById($id)
    {
        return $this->entityManager->getRepository(User::class)
                        ->findOneBy(['id' => $id]);
    }

    /**
     * Checks that the given password is correct.
     * 
     * @param User $user
     * @param string $password
     * @return boolean
     */
    public function validatePassword($user, $password)
    {
        $bcrypt = new Bcrypt();
        return $bcrypt->verify($password, $user->getPassword());
    }

    /**
     * Generates a password reset token and save to database for this user. This token is then stored in database and 
     * sent to the user's E-mail address. When the user clicks the link in E-mail message, he is 
     * directed to the Set Password page.
     * 
     * @param User $user
     */
    public function generatePasswordResetToken(User $user)
    {
// Generate a token.
        $token = Rand::getString(32, '0123456789abcdefghijklmnopqrstuvwxyz', true);
        $user->setPwdResetToken($token);
        $user->setPwdResetTokenDate(new \DateTime());
    }

    /**
     * This method is used to change the password for the given user. To change the password,
     * one must know the old password.
     * 
     * @param User $user
     * @param array $data
     * @return boolean
     */
    public function changePassword($user, $data)
    {
        $oldPassword = $data['old_password'];

// Check that old password is correct
        if (!$this->validatePassword($user, $oldPassword)) {
            return false;
        }
        $newPassword = $data['new_password'];

// Check password length
        if (strlen($newPassword) < 6 || strlen($newPassword) > 64) {
            return false;
        }
        $this->updateValidUsersPassword($user, $newPassword, false);
        return true;
    }

    /**
     * Get all Role object associated with this user
     * 
     * @return ArrayCollection of Role entities
     */
    public function fetchAllRoles()
    {
        return $this->entityManager->getRepository(Role::class)
                        ->findAll();
    }

    /**
     * Get Role entity by role name
     * 
     * @param string $roleName
     * @return Role
     */
    public function getRoleByName($roleName)
    {
        $role = $this->entityManager->getRepository(Role::class)->findOneBy(
                array('name' => $roleName));

        return $role;
    }

    /**
     * Get  ArrayCollection of UserRoleMap entity objects
     * 
     * @param integer  $userId
     * @return ArrayCollection of UserRoleMap entities
     */
    public function getRolesByUserId($userId)
    {
        return $this->entityManager->getRepository(UserRoleMap::class)->findBy(
                        array('user_id' => $userId));
    }

    /**
     * Validate forgotten password for an redirect as required.
     * 
     * @param ZendForm $form
     * @param array $params
     * @return Result
     */
    public function validateForgottenPasswordForm(\AclUser\Form\ForgottenPasswordForm $form, $params): Result
    {
        $form->setData($params);
        $result = new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, []);
        if ($form->isValid()) {
            $data = $form->getData();
            // Look for the user with such email.
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['email' => $data['email']]);
            if ($user != null) {
                // Generate a new password for user and send an E-mail 
                // notification about that.
                $this->generatePasswordResetToken($user);
                $this->entityManager->flush();
                $this->sendForgottenPasswordEmail($user);
                $result = new Result(Result::SUCCESS, $user, ['success' => 'An e-mail has been sent your e-mail address.', 'email-success' => $data['email']]);
            } else {
                $result = new Result(
                        Result::SUCCESS, null, ['error' => 'This e-mail address does not have an account.', 'email' => $data['email']]);
            }
        }
        return $result;
    }

    /**
     * Validate 
     * 
     * @param \AclUser\Form\RegistrationForm $form
     * @param array $params post parameters
     * @param boolean $withCaptcha whether user is being created by new user or admin 
     * @return Result
     */
    public function validateRegistrationForm(\AclUser\Form\RegistrationForm $form, $params, $withCaptcha): Result
    {
        $form->setData($params);
        $result = new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, []);
        if ($form->isValid()) {
            $data = $form->getData();
            // Look for the user with such email.
            if ($this->checkUserExists($data['email'])) {
                $form->get('email')->setMessages(['There is already an account with this e-mail address.']);
            } else {
                $result = $this->createNewUser($data, $withCaptcha);
            }
        }
        return $result;
    }

    /**
     * Create System user and set messages and send e-mail depending if admin created user
     * 
     * @param array $data post parameters
     * @param boolean $withCaptcha indicates whether admin or anon is creating user
     * @return Result
     */
    protected function createNewUser($data, $withCaptcha)
    {
        $user = new User();
        $user->setPhoto(false);
        $user->setEmail($data['email']);
        $user->setFullName($data['full_name']);
        if ($withCaptcha) {
            $this->generatePasswordResetToken($user);
        }
        $user->setStatus($withCaptcha ? User::STATUS_RETIRED : User::STATUS_ACTIVE);
        $user->setDateCreated(new \DateTime());
        $this->updateValidUsersPassword($user, $data['password'], true);
        $this->giveUserBasicRole($user);
        if ($withCaptcha) {
            $this->sendConfirmNewAccountEmail($user);
            $message = ['success' => 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.'];
        } else {
            $message = ['success' => 'Account Created Successfully!'];
        }
        return new Result(Result::SUCCESS, $user, $message);
    }

    /**
     * Send e-mail when new user registers with application
     * 
     * @param User $user the newly created user
     * @param boolean $social whether account was created through social provider
     */
    public function sendConfirmNewAccountEmail(User $user, $social = false)
    {
        $this->mailMessage
                ->setTo($user->getEmail(), $user->getFullName())
                ->setSubject('Confirm Account Email')
                ->setViewScript('acl-user/email/confirm-account-email')
                ->setViewParams(['user' => $user, 'token' => $user->getPwdResetToken(), 'social' => $social])
                ->embedImageFromSrc()
                ->setLayoutTemplate('layout/email-layout')
                ->sendEmailBasedOnViewScript();
    }

    /**
     * Send forgotten password e-mail with reset token 
     * 
     * @param User $user
     */
    protected function sendForgottenPasswordEmail(User $user)
    {
        $this->mailMessage
        ->setTo($user->getEmail(), $user->getFullName())
        ->setSubject('Password Reset')
        ->setViewScript('acl-user/email/forgotten-password-email')
        ->setLayoutTemplate('layout/email-layout')
        ->setViewParams(['user' => $user, 'token' => $user->getPwdResetToken()])
        ->setLayoutImages(['logo' => ['type' => 'image/png', 'filepath' => './public/img/icons/aics_logo.png']])
        ->embedImageFromSrc()
                ->sendEmailBasedOnViewScript();
    }

    /**
     * Get the file path of the present user's image
     * 
     * @param int $id
     * @param boolean $permitted  whether user is permitted to view the image
     * 
     * @return string absolute file path to image of user
     */
    public function getUserPhotoLocationById($id, $permitted)
    {
        $user = $this->getUserById($id);
        $filepath = self::USER_IMAGE_FOLDER . 'avatar.png';
        if ($user && $user->getPhoto() && $permitted) {
            $filepath = self::USER_IMAGE_FOLDER . $user->getId() . '.png';
        }
        return $filepath;
    }

    /**
     * Validate change password form
     * 
     * @param \AclUser\Form\ChangePasswordForm $form
     * @param array $params
     * @param Acluser\Entity\user $user
     * @return Result
     */
    public function validateChangePasswordForm(\AclUser\Form\ChangePasswordForm $form, $params, $user): Result
    {
        $form->setData($params);
        $result = new Result(
                Result::FAILURE, null, ['error' => 'Form is not valid.']);

// Validate form
        if ($form->isValid()) {
// Get filtered and validated data
            $data = $form->getData();
            if (array_key_exists('old_password', $data) && !$this->validatePassword($user, $data['old_password'])) {
                $form->get('old_password')->setMessages(['Old password is not correct']);
            } else {
                // note that form fails validation if form has old_password but params do not
                $this->updateValidUsersPassword($user, $data['new_password']);

                $result = new Result(
                        Result::SUCCESS, null, ['welcome' => 'Your password has been updated.']);
            }
        }
        return $result;
    }

    /**
     * Check that password reset token belongs to a user in the database and that 
     * it has not expired
     * 
     * @param string $token the password reset token
     * @return Result
     */
    public function checkResetToken($token): Result
    {
        $user = $this->getUserByPasswordResetToken($token);
        return $this->checkPasswordResetTokenForUser($user);
    }

    /**
     * Validate reset password form and complete logic as appropriate
     * 
     * @param \AclUser\Form\ResetPasswordForm $form
     * @param array $params post parameters
     * @return Result 
     */
    public function validateResetPasswordForm(\AclUser\Form\ResetPasswordForm $form, $params): Result
    {
        $form->setData($params);
        $result = new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['error' => 'Form is not valid.']);
        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->getUserByPasswordResetToken($data['token']);
            $userResult = $this->checkPasswordResetTokenForUser($user);
            if (!$userResult->isValid()) {
                $result = $userResult;
            } else if ($data['email'] !== $user->getEmail()) {
                $form->get('email')->setMessages(['This email address does not correspond to the submitted password reset token']);
            } else {
                // Remove password reset token
                $user->setPwdResetToken(null);
                $user->setPwdResetTokenDate(null);
                $this->updateValidUsersPassword($user, $data['new_password']);
                $result = new Result(Result::SUCCESS, $user, ['success' => 'Your password has been updated and you have been successfully logged in.']);
            }
        }
        return $result;
    }

    /**
     * Check that user is not null and that their password reset token has not expired
     * Add messages and result validity to Result depending on user status
     * 
     * @todo update type declaration for $user to ?User (incompatible with phpdoc version) 
     * @param User|null $user
     * @param string $message
     * @return Result
     */
    protected function checkPasswordResetTokenForUser($user, $message = null): Result
    {
        if ($user == null) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['error' => 'No user found for this reset token.']);
        }
        $tokenDate = $user->getPwdResetTokenDate();
        $currentDate = strtotime('now');
        if ($currentDate - $tokenDate->getTimestamp() > self::PASSWORD_RESET_TOKEN_VALIDITY) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['error' => 'The password reset token has expired.']);
        }
        return new Result(Result::SUCCESS, $user, [$message]);
    }

    /**
     * Get user from database by password reset token
     * 
     * @param string $token the password reset token
     * @return User|null if user is not found in database
     */
    protected function getUserByPasswordResetToken($token)
    {
        return $this->entityManager->getRepository(User::class)
                        ->findOneBy(['pwdResetToken' => $token]);
    }

    /**
     * Hash password and update database with hash for this user
     * 
     * @param User $user entity object
     * @param string $password un-hashed password
     * @param boolean $newUser whether this a newly created entity
     */
    public function updateValidUsersPassword(User $user, $password, $newUser = false)
    {
        $bcrypt = new Bcrypt();
        $passwordHash = $bcrypt->create($password);
        $user->setPassword($passwordHash);
        if ($newUser) {
            // Add the entity to the entity manager.
            $this->entityManager->persist($user);
        }
        // Apply changes to database.
        $this->entityManager->flush();
    }

    /**
     * Check that token belongs to a user then change their status to active if 
     * it does and update pwdResetToken and date to null. Either was assign 
     * feedback messages to the returned Result object
     * 
     * @param string $token
     * @return Result
     */
    public function activateAccountByToken($token)
    {
        $result = new Result(Result::FAILURE, null, ['error' => 'No user found for this account activation token.']);
        $user = $this->getUserByPasswordResetToken($token);
        if ($user != null) {
            $user->setPwdResetToken(null);
            $user->setPwdResetTokenDate(null);
            $user->setStatus(User::STATUS_ACTIVE);
            $this->entityManager->flush();
            $result = new Result(Result::SUCCESS, null, ['success' => 'Your account has been activated. Log in with your e-mail and password or Social Media provider.']);
        }
        return $result;
    }

    /**
     * Get entity object for the user and update whether they have uploaded their photo
     * 
     * @param integer $id the id of the user
     * @param boolean $status the status of the user
     */
    public function updateUserPhoto($id, $status)
    {
        $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['id' => $id]);
        if ($user) {
            $user->setPhoto($status);
            $this->entityManager->flush();
        }
    }

    /**
     * Validate user photo upload and perform transformation on same
     * 
     * @param boolean $isPost
     * @param AclUser\Form\RotateAndResizeImageForm $form
     * @param \Zend\Mvc\Controller\Plugin\Params $params
     * @param integer $userId
     * @return array
     */
    public function validatePhotoUploadForm($isPost, $form, \Zend\Mvc\Controller\Plugin\Params $params, $userId)
    {
        $result = ['success' => false, 'errors' => ['Form Was Not Submitted by Post']];
        if ($isPost) {
            $data = array_merge_recursive(
                    $params->fromPost(), $params->fromFiles()
            );
            $form->setData($data);
            if ($form->isValid()) {
                $data = $form->getData();
                unset($data['file']);
                $rotator = new RotateAndResizeImageFile();
                $result['success'] = $rotator->rotateAndResize($data, $userId);
                $result['success'] ? $this->updateUserPhoto($userId, true) : null;
                $result['errors'] = $result['success'] ? [] : $rotator->getErrorMessages();
            } else {
                $result['errors'] = $form->getMessages();
            }
        }
        return $result;
    }

    /**
     * Pre-populate BasicProfileForm with the existing values for user corresponding to the user id
     * 
     * @param \AclUser\Form\BasicProfileForm  $form
     * @param integer $id 
     */
    public function prepopulateUserProfile($form, $id)
    {

        $user = $this->entityManager->getRepository(User::class)
                ->findOneBy(['id' => $id]);
        if ($user) {
            $form->get('full_name')->setAttribute('placeholder', $user->getFullName());
            $form->get('email')->setAttribute('placeholder', $user->getEmail());
        }
        return isset($user) ? true : false;
    }

    /**
     * Validate posted parameters from BasicProfileForm and update user if they are valid
     * 
     * @param \AclUser\Form\BasicProfileForm $form
     * @param array $params the post parameters
     * @param integer $id the logged in user id or the id of the user being updated
     * @return boolean
     */
    public function validateBasicProfileForm($form, $params, $id)
    {
        $success = false;
        $form->setData($params);
        if ($form->isValid()) {
            $data = $form->getData();
            $user = $this->entityManager->getRepository(User::class)
                    ->findOneBy(['id' => $id]);
            if ($user) {
                $success = true;
                $user->setFullName($data['full_name']);
                $user->setEmail($data['email']);
                $this->entityManager->flush();
            }
        }
        return $success;
    }

    /**
     * Translate all error messages 
     * 
     * @param Translate\Mvc\Controller\Plugin\TranslateControllerPlugin $translateContollerPlugin translator controller plugin
     * @param array $errorMessages untranslated error messages
     * @return array of translated error messages
     */
    public function getTranslatedErrorMesssages($translateContollerPlugin, $errorMessages)
    {
        $errors = [];
        foreach ($errorMessages as $error) {
            $errors[] = $translateContollerPlugin->translate($error);
        }
        return $errors;
    }

    /**
     * Get an array of all locales that (could be) available to application
     * 
     * @return array
     */
    public function getAllLocales()
    {
        return $this->languageManager->getAllLocales();
    }

}
