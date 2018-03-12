<?php

/**
 * Class UserManagerTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;

/**
 * Test various aspects of AclUser\Service\UserManagerServiceTest
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class UserManagerServiceTest extends AbstractHttpControllerTestCase
{

    protected $builder;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ServiceMockBuilder::getConfig());
        parent::setUp();
        $this->builder = new ServiceMockBuilder($this);
        $this->builder->initialiseEntityManagerMock();
    }

    /**
     * 
     * @param boolean $mockMailViewMessage whether to mock AclUser\Mail\MailMessage before creation
     * @return \AclUser\Service\UserManager
     */
    protected function getUserManagerService($mockMailViewMessage = false)
    {
        $mockMailViewMessage ? $this->builder->mockMailViewMessage() : null;
        return $this->builder->buildServiceWithOptionalSessionManager(\AclUser\Service\UserManager::class);
    }

    public function testUserManagerServiceCanGetUserByEmailAddress()
    {
        $this->builder->setupUser();
        $service = $this->getUserManagerService();
        $user = $service->getUserByEmailAddress('email@mailserver.com');
        $this->assertTrue($user->getFullName() == 'Peter Parker');
        $this->assertTrue($user->getEmail() == 'email@mailserver.com');
        $this->assertTrue($user->getId() == 1);
    }

    public function testUserManagerServiceCanGetRoleByName()
    {
        $this->builder->setupRole();
        $service = $this->getUserManagerService();
        $role = $service->getRoleByName('basic');
        $this->assertTrue($role->getName() == 'basic');
    }

    public function testUserManagerServiceCanFailToValidateRegistrationForm()
    {
        $form = $this->builder->mockForm(\AclUser\Form\RegistrationForm::class, false);
        $service = $this->getUserManagerService();
        $result = $service->validateRegistrationForm($form, [], false);
        $this->assertTrue(!$result->isValid());
    }

    public function testUserManagerServiceCanFailToValidateRegistrationWithSameEmailForm()
    {
        $this->builder->setupUser();
        $params = ['email' => 'email@mailserver.com'];
        $form = $this->builder->mockForm(\AclUser\Form\RegistrationForm::class, true, $params);
        $service = $this->getUserManagerService();
        $result = $service->validateRegistrationForm($form, $params, true);
        $this->assertTrue(!$result->isValid());
    }

    public function testUserManagerServiceCanValidateRegistrationWithoutCaptcha()
    {
        $params = ['email' => 'email@mailserver.com', 'full_name' => 'Peter Parker', 'password' => 'password'];
        $this->builder->setupUser(false, false);
        $this->builder->setupRole(true, 'basic', false);
        $form = $this->builder->mockForm(\AclUser\Form\RegistrationForm::class, true, $params);
        $service = $this->getUserManagerService();
        $result = $service->validateRegistrationForm($form, $params, false);
        $this->assertTrue($result->isValid());
    }

    public function testUserManagerServiceCanValidateRegistrationWithCaptcha()
    {
        $params = ['email' => 'email@mailserver.com', 'full_name' => 'Peter Parker', 'password' => 'password'];
        $this->builder->setupUser(false, false);
        $this->builder->setupRole(true, 'basic', false);
        $form = $this->builder->mockForm(\AclUser\Form\RegistrationForm::class, true, $params);
        $service = $this->getUserManagerService(true);
        $result = $service->validateRegistrationForm($form, $params, true);
        $this->assertTrue($result->isValid());
    }

    public function testUserManagerServiceCanUpdateUser()
    {
        $data = [
            'email' => 'email@mailserver.com',
            'full_name' => 'Peter Parker',
            'password' => 'password',
            'status' => true
        ];
        $this->builder->setupUser();
        $service = $this->getUserManagerService();
        $user = $this->builder->getNewUser();
        $true = $service->updateUser($user, $data);
        $this->assertTrue($true);
    }

    public function testUserManagerServiceCannotUpdateUserWithEmailAddressThatIsAlreadyUsed()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Another user with email address email@mailserver.com already exists');
        $data = [
            'email' => 'email@mailserver.com',
            'full_name' => 'Peter Parker',
            'password' => 'password',
            'status' => true
        ];
        $this->builder->setupUser();
        $service = $this->getUserManagerService();
        $user = $this->builder->getNewUser('basic', 'old_email@mailserver.com');
        $true = $service->updateUser($user, $data);
    }

    public function testUserManagerServiceCanGetUserById()
    {
        $this->builder->setupUser();
        $service = $this->getUserManagerService();
        $user = $service->getUserById(1);
        $this->assertTrue($user->getFullName() == 'Peter Parker');
        $this->assertTrue($user->getEmail() == 'email@mailserver.com');
        $this->assertTrue($user->getId() == 1);
    }

    public function testUserManagerServiceCanValidatePasswordAndFail()
    {
        $service = $this->getUserManagerService();
        $false = $service->validatePassword($this->builder->getNewUser(), 'wrong_password');
        $this->assertNotTrue($false);
    }

    public function testUserManagerServiceCanValidatePasswordAndSucceed()
    {
        $service = $this->getUserManagerService();
        $true = $service->validatePassword($this->builder->getNewUser(), 'Secret#p@ss');
        $this->assertTrue($true);
    }

    public function testUserManagerServiceCanChangePasswordWithoutSuccess()
    {
        $data = [
            'old_password' => 'wrong_old_password'
        ];
        $service = $this->getUserManagerService();
        $false = $service->changePassword($this->builder->getNewUser(), $data);
        $this->assertNotTrue($false);
    }

    public function testUserManagerServiceCanChangePasswordThatIsTooShortWithoutSuccess()
    {
        $data = [
            'old_password' => 'Secret#p@ss',
            'new_password' => 'short'
        ];
        $service = $this->getUserManagerService();
        $false = $service->changePassword($this->builder->getNewUser(), $data);
        $this->assertNotTrue($false);
    }

    public function testUserManagerServiceCanChangePasswordThatIsTooLongWithoutSuccess()
    {
        $data = [
            'old_password' => 'Secret#p@ss',
            'new_password' => str_pad('a', 65)
        ];
        $service = $this->getUserManagerService();
        $false = $service->changePassword($this->builder->getNewUser(), $data);
        $this->assertNotTrue($false);
    }

    public function testUserManagerServiceCanChangePasswordWithSuccess()
    {
        $data = [
            'old_password' => 'Secret#p@ss',
            'new_password' => 'new_password'
        ];
        $service = $this->getUserManagerService();
        $true = $service->changePassword($this->builder->getNewUser(), $data);
        $this->assertTrue($true);
    }

    public function testUserManagerServiceCanFetchAllRoles()
    {
        $this->builder->setupRole();
        $service = $this->getUserManagerService();
        $allUsers = $this->builder->getAllRoles();
        $this->builder->setupRepoFinderMethod(['findAll' => $allUsers]);
        $result = $service->fetchAllRoles();
        $this->assertTrue($allUsers == $result, 'UserManager -> fetchAllRoles should return all roles');
    }

    public function testUserManagerServiceCanGetRolesByUserId()
    {
        $this->builder->setupUserRoleMap();
        $service = $this->getUserManagerService();
        $out = $service->getRolesByUserId(1);
        $this->assertTrue($out == []);
    }

    public function testUserManagerServiceCanFailToValidateForgottenPasswordForm()
    {
        $form = $this->builder->mockForm(\AclUser\Form\ForgottenPasswordForm ::class, false);
        $service = $this->getUserManagerService();
        $result = $service->validateForgottenPasswordForm($form, []);
        $this->assertNotTrue($result->isValid());
    }

    public function testUserManagerServiceCanValidateForgottenPasswordFormWithoutValidEmailAddress()
    {
        $params = [
            'email' => 'invalid@mailserver.com'
        ];
        $this->builder->setupUser(false);
        $form = $this->builder->mockForm(\AclUser\Form\ForgottenPasswordForm ::class, true, $params);
        $service = $this->getUserManagerService();
        $result = $service->validateForgottenPasswordForm($form, $params);
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'This e-mail address does not have an account.');
        $this->assertArrayHasKey('email', $messages);
        $this->assertTrue($messages['email'] == 'invalid@mailserver.com');
    }

    public function testUserManagerServiceCanValidateForgottenPasswordFormWithValidEmailAddress()
    {
        $params = [
            'email' => 'invalid@mailserver.com'
        ];
        $this->builder->setupUser(true);
        $form = $this->builder->mockForm(\AclUser\Form\ForgottenPasswordForm ::class, true, $params);
        $service = $this->getUserManagerService(true);
        $result = $service->validateForgottenPasswordForm($form, $params);
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('success', $messages);
        $this->assertTrue($messages['success'] == 'An e-mail has been sent your e-mail address.');
        $this->assertArrayHasKey('email-success', $messages);
        $this->assertTrue($messages['email-success'] == 'invalid@mailserver.com');
    }

    public function testUserManagerServiceCanGetUserPhotoLocationByIdIfUserHasNoPhoto()
    {
        $this->builder->setupUser(true, true, ['setPhoto' => true]);
        $service = $this->getUserManagerService();
        $filepath = $service->getUserPhotoLocationById(1, false);
        $this->assertTrue($filepath == './data/media/user-images/avatar.png');
    }

    public function testUserManagerServiceCanGetUserPhotoLocationByIdIfUserDoeshavePhoto()
    {
        $this->builder->setupUser(true, true, ['setPhoto' => true]);
        $service = $this->getUserManagerService();
        $filepath = $service->getUserPhotoLocationById(1, true);
        $this->assertTrue($filepath == './data/media/user-images/1.png');
    }

    public function testUserManagerServiceCanFailToValidateChangePasswordForm()
    {
        $form = $this->builder->mockForm(\AclUser\Form\ChangePasswordForm ::class, false);
        $service = $this->getUserManagerService();
        $result = $service->validateChangePasswordForm($form, [], null);
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'Form is not valid.');
    }

    public function testUserManagerServiceCanValidateChangePasswordFormWithWrongOldpasswordAndReturnInvalidResult()
    {
        $params = [
            'old_password' => 'wrong_old_password'
        ];
        $form = $this->builder->mockForm(\AclUser\Form\ChangePasswordForm ::class, true, $params);
        $service = $this->getUserManagerService();
        $result = $service->validateChangePasswordForm($form, $params, $this->builder->getNewUser());
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'Form is not valid.');
    }

    public function testUserManagerServiceCanValidateChangePasswordFormAndReturnValidResult()
    {
        $params = [
            'old_password' => 'Secret#p@ss',
            'new_password' => 'new_password'
        ];
        $form = $this->builder->mockForm(\AclUser\Form\ChangePasswordForm ::class, true, $params);
        $service = $this->getUserManagerService();
        $result = $service->validateChangePasswordForm($form, $params, $this->builder->getNewUser());
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('welcome', $messages);
        $this->assertTrue($messages['welcome'] == 'Your password has been updated.');
    }

    public function testUserManagerServiceCanCheckResetTokenAndFailNoUser()
    {
        $service = $this->getUserManagerService();
        $this->builder->setupUser(false);
        $result = $service->checkResetToken('token');
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'No user found for this reset token.');
    }

    public function testUserManagerServiceCanCheckResetTokenAndFailOutdatedToken()
    {
        $service = $this->getUserManagerService();
        $dateTime = new \DateTime();
        $dateTime->setDate(2017, 2, 3);
        $this->builder->setupUser(true, true, ['setPwdResetTokenDate' => $dateTime]);
        $result = $service->checkResetToken('token');
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'The password reset token has expired.');
    }

    public function testUserManagerServiceCanCheckResetTokenAndSucceed()
    {
        $service = $this->getUserManagerService();
        $dateTime = new \DateTime();
        $this->builder->setupUser(true, true, ['setPwdResetTokenDate' => $dateTime]);
        $result = $service->checkResetToken('token');
        $this->assertTrue($result->isValid());
    }

    public function testUserManagerServiceCanFailToValidateResetPasswordForm()
    {
        $form = $this->builder->mockForm(\AclUser\Form\ResetPasswordForm ::class, false);
        $service = $this->getUserManagerService();
        $result = $service->validateResetPasswordForm($form, []);
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'Form is not valid.');
    }

    public function testUserManagerServiceCanFailToValidateResetPasswordFormWithNoUser()
    {
        $data = [
            'token' => 'token',
        ];
        $form = $this->builder->mockForm(\AclUser\Form\ResetPasswordForm ::class, true, $data);
        $this->builder->setupUser(false);
        $service = $this->getUserManagerService();
        $result = $service->validateResetPasswordForm($form, $data);
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'No user found for this reset token.');
    }

    public function testUserManagerServiceCanFailToValidateResetPasswordFormWithWrongEmail()
    {
        $data = [
            'token' => 'reset_token',
            'email' => 'wrong_email@mailserver.com',
            'new_password' => 'new_password'
        ];
        $dateTime = new \DateTime();
        $form = $this->builder->mockForm(\AclUser\Form\ResetPasswordForm ::class, true, $data);
        $this->builder->setupUser(true, true, ['setPwdResetToken' => 'reset_token', 'setPwdResetTokenDate' => $dateTime]);
        $service = $this->getUserManagerService();
        $result = $service->validateResetPasswordForm($form, $data);
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'Form is not valid.');
    }

    public function testUserManagerServiceCanValidateResetPasswordForm()
    {
        $data = [
            'token' => 'reset_token',
            'email' => 'email@mailserver.com',
            'new_password' => 'new_password'
        ];
        $dateTime = new \DateTime();
        $form = $this->builder->mockForm(\AclUser\Form\ResetPasswordForm ::class, true, $data);
        $this->builder->setupUser(true, true, ['setPwdResetToken' => 'reset_token', 'setPwdResetTokenDate' => $dateTime]);
        $service = $this->getUserManagerService();
        $result = $service->validateResetPasswordForm($form, $data);
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('success', $messages);
        $this->assertTrue($messages['success'] == 'Your password has been updated and you have been successfully logged in.');
    }

    public function testUserManagerServiceCanFailToActivateAccountByToken()
    {
        $this->builder->setupUser(false);
        $service = $this->getUserManagerService();
        $result = $service->activateAccountByToken('token');
        $this->assertNotTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('error', $messages);
        $this->assertTrue($messages['error'] == 'No user found for this account activation token.');
    }

    public function testUserManagerServiceCanActivateAccountByToken()
    {
        $this->builder->setupUser(true);
        $service = $this->getUserManagerService();
        $result = $service->activateAccountByToken('token');
        $this->assertTrue($result->isValid());
        $messages = $result->getMessages();
        $this->assertArrayHasKey('success', $messages);
        $this->assertTrue($messages['success'] == 'Your account has been activated. Log in with your e-mail and password or Social Media provider.');
    }

    public function testUserManagerServiceCanUpdateUserPhoto()
    {
        $user = $this->builder->setupUser(true, true, ['setPhoto' => false]);
        $service = $this->getUserManagerService();
        $this->assertTrue($user->getPhoto() == false, 'user did not have photo true before calling updateUserPhoto');
        $null = $service->updateUserPhoto(1, true);
        $this->assertNull($null, 'method updateUserPhoto did not return null');
        $this->assertTrue($user->getPhoto() == true, 'method updateUserPhoto did did not change photo of user to true');
    }

    public function testUserManagerServiceCanGetTranslatedErrorMesssages()
    {
        $translateContollerPlugin = $this->builder->getMocked(\Translate\Mvc\Controller\Plugin\TranslateControllerPlugin::class, ['translate' => 'translated message']);

        $service = $this->getUserManagerService();
        $errors = $service->getTranslatedErrorMesssages($translateContollerPlugin, ['error']);
        $this->assertTrue(is_array($errors), 'getTranslatedErrorMesssages should return an array');
        $this->assertTrue($errors[0] == 'translated message', 'getTranslatedErrorMesssages should translate each value in array to "translated message"');
    }

    public function testUserManagerServiceCanValidatePhotoUploadFormAndFailNotPost()
    {
        $params = $this->builder->getMocked(\Zend\Mvc\Controller\Plugin\Params::class, ['fromPost' => [], 'fromFiles' => ['file' => null]]);
        $form = $this->builder->mockForm(\AclUser\Form\RotateAndResizeImageForm::class, false);
        $service = $this->getUserManagerService();
        $result = $service->validatePhotoUploadForm(false, $form, $params, 1);
        $this->assertTrue(is_array($result), 'validatePhotoUploadForm should return an array');
        $this->assertArrayHasKey('success', $result);
        $this->assertNotTrue($result['success'], 'result success should be false');
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue(is_array($result['errors']), 'function should return an array of errors on failure');
    }

    public function testUserManagerServiceCanValidatePhotoUploadFormAndFailFromNotValid()
    {
        $params = $this->builder->getMocked(\Zend\Mvc\Controller\Plugin\Params::class, ['fromPost' => [], 'fromFiles' => ['file' => null]]);
        $form = $this->builder->mockForm(\AclUser\Form\RotateAndResizeImageForm::class, false, [], ['getMessages' => ['error']]);
        $service = $this->getUserManagerService();
        $result = $service->validatePhotoUploadForm(true, $form, $params, 1);
        $this->assertTrue(is_array($result), 'validatePhotoUploadForm should return an array');
        $this->assertArrayHasKey('success', $result);
        $this->assertNotTrue($result['success'], 'result success should be false');
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue(is_array($result['errors']), 'function should return an array of errors on failure');
        $this->assertTrue($result['errors'][0] == 'error', 'function should value passed for getMessages');
    }

    public function testUserManagerServiceCanValidatePhotoUploadFormAndFailRotateNotValid()
    {
        $postParams = ['file-name' => 'file.png'];
        $params = $this->builder->getMocked(\Zend\Mvc\Controller\Plugin\Params::class, ['fromPost' => $postParams, 'fromFiles' => ['file' => null]]);
        $form = $this->builder->mockForm(\AclUser\Form\RotateAndResizeImageForm::class, true, $postParams, ['getMessages' => ['error']]);
        $service = $this->getUserManagerService();
        $result = $service->validatePhotoUploadForm(true, $form, $params, 1);
        $this->assertTrue(is_array($result), 'validatePhotoUploadForm should return an array');
        $this->assertArrayHasKey('success', $result);
        $this->assertNotTrue($result['success'], 'result success should be false');
        $this->assertArrayHasKey('errors', $result);
        $this->assertTrue(is_array($result['errors']), 'function should return an array of errors on failure');
        /* CANNOT TEST THIS FUNCTION ANY FURTHER */
    }

    public function testUserManagerServiceCanValidateBasicProfileFormAndFailWithoutUser()
    {
        $postParams = [];
        $this->builder->setupUser(false);
        $form = $this->builder->mockForm(\AclUser\Form\BasicProfileForm::class, true, $postParams);
        $service = $this->getUserManagerService();
        $result = $service->validateBasicProfileForm($form, $postParams, 1);
        $this->assertNotTrue($result, 'validatePhotoUploadForm should return boolean false');
    }

    public function testUserManagerServiceCanValidateBasicProfileFormAndFailinvalidForm()
    {
        $postParams = [];
        $this->builder->setupUser(false);
        $form = $this->builder->mockForm(\AclUser\Form\BasicProfileForm::class, false, $postParams);
        $service = $this->getUserManagerService();
        $result = $service->validateBasicProfileForm($form, $postParams, 1);
        $this->assertNotTrue($result, 'validatePhotoUploadForm should return boolean false');
    }

    public function testUserManagerServiceCanValidateBasicProfileFormAndSucceedWithValidForm()
    {
        $postParams = ['full_name' => 'full_name', 'email' => 'email@servermail.com'];
        $this->builder->setupUser(true);
        $form = $this->builder->mockForm(\AclUser\Form\BasicProfileForm::class, true, $postParams);
        $service = $this->getUserManagerService();
        $result = $service->validateBasicProfileForm($form, $postParams, 1);
        $this->assertTrue($result, 'validatePhotoUploadForm should return boolean true');
    }

    public function testUserManagerServiceCanPrepopulateBasicProfileForm()
    {

        $form = new \AclUser\Form\BasicProfileForm();
        $user = $this->builder->setupUser(true);
        $service = $this->getUserManagerService();
        $service->prepopulateUserProfile($form, 1);
        $this->assertEquals($form->get('full_name')->getAttribute('placeholder'), $user->getFullName());
        $this->assertEquals($form->get('email')->getAttribute('placeholder'), $user->getEmail());
    }

    public function testUserManagerCanGetAllLocales()
    {
        $service = $this->getUserManagerService();
        $locales = $service->getAllLocales();
        $this->assertTrue(is_array($locales));
        $this->assertContains('en_GB', $locales);
    }

}
