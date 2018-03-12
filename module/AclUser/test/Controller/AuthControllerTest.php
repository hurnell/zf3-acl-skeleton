<?php

/**
 * Class AuthControllerTest 
 *
 * @package     AclUserTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUserTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ControllerMockBuilder;
use Zend\Authentication\Result;

/**
 * 
  drwxr-x---  2 mysql adm     4096 Feb 24 14:28 ./
  drwxr-xr-x 16 root  syslog  4096 Feb 24 06:25 ../
  -rw-r-----  1 mysql adm        0 Feb 24 14:45 error.log
  -rw-r-----  1 mysql adm    11534 Feb 24 14:47 hurnell.log
 */

/**
 * Test various aspects of AclUser\Controller\AuthController
 */
class AuthControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * Instance of class that is used to mock AclUser\Service\UserManager,
     * Zend\Authentication\AuthenticationService and replace AccessControlList 
     * 
     * @var ControllerMockBuilder 
     */
    protected $builder;

    /**
     * FlashMessengerTestCaseHelper is created in MockBuilder
     * 
     * @see \AclUserTest\Mock\MockBuilder::createFlashMessengerTestCaseHelper
     * @var \AclUserTest\Mock\FlashMessengerTestCaseHelper 
     */
    protected $fmtc;

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ControllerMockBuilder::getConfig());
        parent::setUp();
        $this->builder = new ControllerMockBuilder($this);
        $this->fmtc = $this->builder->createFlashMessengerTestCaseHelper();
    }

    /**
     * Test that /user-auth/login request renders the required HTML form for guest user
     */
    public function testGuestUserCanAccessLoginPageWithForm()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/login', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="login-form"]');
        // ensure that input with name email exists 
        $this->assertQuery('input[name="email"][type="email"]');
        // ensure that input with name password exists 
        $this->assertQuery('input[name="password"][type="password"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="remember_me"][type="checkbox"]');
        $this->assertQuery('a[href*="user/forgotten-password"]');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
    }

    /**
     * Test that basic user gets redirected when trying to access login page 
     * They are already logged in.
     */
    public function testBasicUserIsRedirectedAwayFromLoginPage()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user-auth/login', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
        $this->assertRedirectRegex('/\/index\/index/');

        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You tried to log in but you are already logged in.');
    }

    public function testBasicUserIsRedirectedAwayFromLogoutPage()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/logout', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('logout');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You tried to log out but you are not logged in.');
    }

    /**
     * Test that basic user gets redirected when trying to access login page 
     * They are already logged in.
     */
    public function testGuestUserLoginFailsWithInvalidCredentials()
    {
        $this->builder->unAuthorised();

        $result = new Result(
                Result::FAILURE, null, ['error' => 'Form is not valid.']);
        $this->builder->setupAuthManagerReturnsResult('validateLoginForm', $result);
        $this->dispatch('/en_GB/user-auth/login', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'Form is not valid.');
    }

    /**
     * Test that guest gets redirected to index/index when login succeeds
     */
    public function testGuestUserLoginSucceedsWithoutRedirect()
    {
        $this->builder->unAuthorised();

        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);
        $this->builder->setupAuthManagerReturnsResult('validateLoginForm', $result);
        $this->dispatch('/en_GB/user-auth/login', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been successfully logged in.');
    }

    /**
     * Test that guest gets redirected to given redirect  when login succeeds
     */
    public function testGuestUserLoginSucceedsWithShortRedirect()
    {
        $this->builder->unAuthorised();

        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);
        $this->builder->setupAuthManagerReturnsResult('validateLoginForm', $result);
        $this->dispatch('/en_GB/user-auth/login', 'POST', ['redirect_url' => '/en_GB/user/profile']);
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
        $this->assertRedirectRegex('/\/user\/profile/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been successfully logged in.');
    }

    /**
     * Test that guest gets redirected to given redirect  when login succeeds
     */
    public function testGuestUserLoginDoesNotRedirectWithInvalidShortRedirect()
    {
        $this->builder->unAuthorised();

        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);
        $this->builder->setupAuthManagerReturnsResult('validateLoginForm', $result);
        $this->dispatch('/en_GB/user-auth/login', 'POST', ['redirect_url' => '////en_GB/user/profile']);
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been successfully logged in.');
    }

    /**
     * Test that error is thrown when redirectUrl is too long
     */
    public function testGuestUserLoginThrowsErrorWithLongRedirect()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/login?redirectUrl=' . str_pad('.', 2049), 'GET');
        $this->assertResponseStatusCode(500);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('login');
    }

    /**
     * Test that error is thrown when redirectUrl is too long
     */
    public function testBasicUserIsRedirectedWhenTheyLogout()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user-auth/logout', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('logout');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been logged out.');
    }

    /**
     * Test that /user-auth/register request renders Registration form
     */
    public function testRegisterUserPageRendersForm()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/register', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="registration-form"]');
        $this->assertQuery('input[name="full_name"][type="text"]');
        // ensure that input with name email exists
        $this->assertQuery('input[name="email"][type="email"]');
        // ensure that input with name password exists
        $this->assertQuery('input[name="password"][type="password"]');
        $this->assertQuery('input[name="confirm_password"][type="password"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="captcha[input]"][type="text"]');
        $this->assertQuery('input[name="captcha[id]"][type="hidden"]');
        $this->assertQuery('img[alt*="CAPTCHA"]');
    }

    /**
     * Test that /user-auth/register request renders Registration form with errors
     */
    public function testRegisterUserPostWithoutSuccess()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::FAILURE, null, ['Form is not valid.']);
        $this->builder->setupUserManagerReturnsResult('validateRegistrationForm', $result);
        $this->dispatch('/en_GB/user-auth/register', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="registration-form"]');
        $this->assertQuery('input[name="full_name"][type="text"]');
        // ensure that input with name email exists
        $this->assertQuery('input[name="email"][type="email"]');
        // ensure that input with name password exists
        $this->assertQuery('input[name="password"][type="password"]');
        $this->assertQuery('input[name="confirm_password"][type="password"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="captcha[input]"][type="text"]');
        $this->assertQuery('input[name="captcha[id]"][type="hidden"]');
        $this->assertQuery('img[alt*="CAPTCHA"]');
    }

    /**
     * Test that /user-auth/register request renders Registration form with errors
     */
    public function testRegisterUserPostWithSuccess()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::SUCCESS, '$user', ['success' => 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.']);
        $this->builder->setupUserManagerReturnsResult('validateRegistrationForm', $result);
        $this->dispatch('/en_GB/user-auth/register', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('register');
        $this->assertRedirectRegex('/\/user-auth\/register/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.');
    }

    /**
     * Test that /user-auth/register request renders Registration form with errors
     */
    public function testRoleUserManagerCanAccessCreateNewUser()
    {
        $this->builder->specificAuthorised('user-manager');

        $this->dispatch('/en_GB/user-auth/create-new-user', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="registration-form"]');
        $this->assertQuery('input[name="full_name"][type="text"]');
        // ensure that input with name email exists
        $this->assertQuery('input[name="email"][type="email"]');
        // ensure that input with name password exists
        $this->assertQuery('input[name="password"][type="password"]');
        $this->assertQuery('input[name="confirm_password"][type="password"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertNotQuery('input[name="captcha[input]"][type="text"]');
        $this->assertNotQuery('input[name="captcha[id]"][type="hidden"]');
        $this->assertNotQuery('img[alt*="CAPTCHA"]');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('create-new-user');
    }

    /**
     * Test that /user-auth/register request renders Registration form with errors
     */
    public function testRoleUserManagerCanPostCreateNewUserWithFailure()
    {
        $this->builder->specificAuthorised('user-manager');

        $result = new Result(Result::FAILURE, null, ['Form is not valid.']);
        $this->builder->setupUserManagerReturnsResult('validateRegistrationForm', $result)->finaliseAcl();
        $this->dispatch('/en_GB/user-auth/create-new-user', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="registration-form"]');
        $this->assertQuery('input[name="full_name"][type="text"]');
        // ensure that input with name email exists
        $this->assertQuery('input[name="email"][type="email"]');
        // ensure that input with name password exists
        $this->assertQuery('input[name="password"][type="password"]');
        $this->assertQuery('input[name="confirm_password"][type="password"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertNotQuery('input[name="captcha[input]"][type="text"]');
        $this->assertNotQuery('input[name="captcha[id]"][type="hidden"]');
        $this->assertNotQuery('img[alt*="CAPTCHA"]');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('create-new-user');
    }

    /**
     * Test that /user-auth/create-new-user request renders Registration form with success and redirect
     */
    public function testRoleUserManagerCanPostCreateNewUserWithSuccess()
    {
        $this->builder->specificAuthorised('user-manager');

        $result = new Result(Result::SUCCESS, '$user', ['success' => 'Account Created Successfully!']);
        $this->builder->setupUserManagerReturnsResult('validateRegistrationForm', $result)->finaliseAcl();
        $this->dispatch('/en_GB/user-auth/create-new-user', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('create-new-user');
        $this->assertRedirectRegex('/\/user-auth\/create-new-user/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'Account Created Successfully!');
    }

    /**
     * 
     */
    public function testResetPasswordActionWithTokenFailure()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/reset-password/failure', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('reset-password');
        $this->assertNotQuery('form[method="post"][name="reset-password-form"]');
    }

    /**
     * 
     */
    public function testResetPasswordActionWithTokenGetAndFailure()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['error' => 'No user found for this reset token.']);
        $this->builder->setupUserManagerReturnsResult('checkResetToken', $result);
        $this->dispatch('/en_GB/user-auth/reset-password/aaaaaa', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('reset-password');
        $this->assertRedirectRegex('/\/user-auth\/reset-password/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'No user found for this reset token.');
    }

    /**
     * 
     */
    public function testResetPasswordActionWithTokenGetAndSuccess()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::SUCCESS, '$user', ['$message']);
        $this->builder->setupUserManagerReturnsResult('checkResetToken', $result);
        $this->dispatch('/en_GB/user-auth/reset-password/aaaaaa', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('reset-password');
        $this->assertQuery('form[method="post"][name="reset-password-form"]');
        $this->assertQuery('input[name="token"][type="hidden"]');
        $this->assertQuery('input[name="email"][type="email"]');
        $this->assertQuery('input[name="new_password"][type="password"]');
        $this->assertQuery('input[name="confirm_new_password"][type="password"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
    }

    /**
     * 
     */
    public function testResetPasswordActionWithTokenPostAndFailure()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::FAILURE_IDENTITY_NOT_FOUND, null, []);
        $this->builder->setupUserManagerReturnsResult('validateResetPasswordForm', $result);
        $this->dispatch('/en_GB/user-auth/reset-password/aaaaaa', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('reset-password');
        $this->assertQuery('form[method="post"][name="reset-password-form"]');
        $this->assertQuery('input[name="token"][type="hidden"]');
        $this->assertQuery('input[name="email"][type="email"]');
        $this->assertQuery('input[name="new_password"][type="password"]');
        $this->assertQuery('input[name="confirm_new_password"][type="password"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
    }

    /**
     * 
     */
    public function testResetPasswordActionWithTokenPostAndSuccess()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::SUCCESS, new \AclUser\Entity\User(), ['success' => 'Your password has been updated and you have been successfully logged in.']);
        $this->builder->setupUserManagerReturnsResult('validateResetPasswordForm', $result)->setupAuthManagerReturnsAnything('loginUser', null);
        $this->dispatch('/en_GB/user-auth/reset-password/aaaaaa', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('reset-password');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'Your password has been updated and you have been successfully logged in.');
    }

    /**
     * 
     */
    public function testBasicUserCanAccessChangePasswordAction()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user-auth/change-password', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('change-password');
    }

    /**
     * 
     */
    public function testBasicUserCanPostFailureChangePasswordAction()
    {
        $this->builder->basicAuthorised();

        $result = new Result(Result::FAILURE, null, ['Form is not valid.']);
        $this->builder->setupUserManagerReturnsResult('validateChangePasswordForm', $result);
        $this->dispatch('/en_GB/user-auth/change-password', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('change-password');
    }

    /**
     * 
     */
    public function testBasicUserCanPostSuccessWithShortPasswordToChangePasswordAction()
    {
        $this->builder->basicAuthorised();

        $result = new Result(
                Result::SUCCESS, null, ['success' => 'Your password has been updated.']);
        $this->builder->setupUserManagerReturnsResult('validateChangePasswordForm', $result)->setupUserManagerReturnsAnything('updateValidUsersPassword', null);
        $this->dispatch('/en_GB/user-auth/change-password', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('change-password');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'Your password has been updated.');
    }

    /**
     * 
     */
    public function testBasicUserCanPostSuccessWithLongPasswordToChangePasswordAction()
    {
        $this->builder->setupUserManagerMockUser(true, 'basic', str_pad('a', 50));

        $result = new Result(
                Result::SUCCESS, null, ['success' => 'Your password has been updated.']);
        $this->builder->setupServiceAuthMock(true)
                ->finaliseAcl()->setupUserManagerReturnsResult('validateChangePasswordForm', $result)
                ->setupUserManagerReturnsAnything('updateValidUsersPassword', null);
        $this->dispatch('/en_GB/user-auth/change-password', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('change-password');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'Your password has been updated.');
    }

    public function testGuestCanAccessConfirmAccountAndRenderExpiredMessage()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user-auth/confirm-account/expired', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('confirm-account');
    }

    public function testGuestCanAccessConfirmAccountAndRedirectAfterFailure()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::FAILURE, null, ['error' => 'No user found for this account activation token.']);
        $this->builder->setupUserManagerReturnsResult('activateAccountByToken', $result);
        $this->dispatch('/en_GB/user-auth/confirm-account/not-expired', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('confirm-account');
        $this->assertRedirectRegex('/\/user-auth\/confirm-account\/expired/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'No user found for this account activation token.');
    }

    public function testGuestCanAccessConfirmAccountAndRedirectAfterSuccess()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::SUCCESS, null, ['success' => 'Your account has been activated. Log in with your e-mail and password or Social Media provider.']);
        $this->builder->setupUserManagerReturnsResult('activateAccountByToken', $result);
        $this->dispatch('/en_GB/user-auth/confirm-account/not-expired', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user-auth');
        $this->assertControllerClass('AuthController');
        $this->assertActionName('confirm-account');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'Your account has been activated. Log in with your e-mail and password or Social Media provider.');
    }

}
