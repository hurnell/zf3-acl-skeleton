<?php

/**
 * Class UserControllerTest 
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
 * Test various aspects of AclUser\Controller\UserController
 *
 * @package     AclUserTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class UserControllerTest extends AbstractHttpControllerTestCase
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
     * Test that /user/forgotten-password request renders the required HTML form
     */
    public function testUserForgottenPasswordRendersForm()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user/forgotten-password', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('form[method="post"][name="reset-password-form"]');
        $this->assertQuery('input[name="email"][type="email"]');
        $this->assertQuery('input[name="csrf"][type="hidden"]');
        $this->assertQuery('input[name="submit"][type="submit"]');
        $this->assertQuery('input[name="captcha[input]"][type="text"]');
        $this->assertQuery('input[name="captcha[id]"][type="hidden"]');
        $this->assertQuery('img[alt*="CAPTCHA"]');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('forgotten-password');
    }

    /**
     * Test that posting (no values) to /en_GB/user/forgotten-password does not
     * redirect and 
     */
    public function testPostToForgottenPasswordAndFormFailsValidation()
    {
        $this->builder->unAuthorised();

        $result = new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, []);
        $this->builder->setupUserManagerReturnsResult('validateForgottenPasswordForm', $result);
        $this->dispatch('/en_GB/user/forgotten-password', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('forgotten-password');
    }

    public function testUserWithoutRolesIsTreatedAsGuest()
    {
        $this->builder->setupUserManagerMockUser(true, 'unknown-role')->setupServiceAuthMock(true, 1)
                ->finaliseAcl();
        $this->dispatch('/en_GB/user/profile', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
        $this->assertRedirectRegex('/\/index\/index/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You do not have permission to visit the requested page.');
    }

    public function testBasicUserCannotAccessRouteWithBadController()
    {
        $this->builder->basicAuthorised();

        try {
            $this->dispatch('/en_GB/bad-controller/profile', 'GET');
        } catch (\Zend\ServiceManager\Exception\ServiceNotFoundException $e) {
            
        }
        $this->assertControllerName('bad-controller');
        $this->assertActionName('profile');
        $this->assertResponseStatusCode(404);
    }

    /**
     * Test that post to en_GB/user/forgotten-password redirects to same when 
     * e-mail address is unknown
     */
    public function testPostToForgottenPasswordFailsWhenEmailDoesNotHaveAccount()
    {
        $this->builder->unAuthorised();

        $result = new Result(Result::SUCCESS, null, ['error' => 'This e-mail address does not have an account.', 'email' => 'email@mailserver.com']);
        $this->builder->setupUserManagerMockUser(false)
                ->setupUserManagerReturnsResult('validateForgottenPasswordForm', $result);
        $this->dispatch('/en_GB/user/forgotten-password', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectRegex('/\/user\/forgotten-password/');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('forgotten-password');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasNamespace('email');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'This e-mail address does not have an account.');
        $this->fmtc->assertFlashMessengerHasMessage('email', 'email@mailserver.com');
    }

    /**
     * Test that post to en_GB/user/forgotten-password redirects to same when user does have an account
     */
    public function testPostToForgottenPasswordSucceedsWhenEmailDoesHaveAccount()
    {
        $this->builder->basicAuthorised();

        $result = new Result(Result::SUCCESS, $this->builder->getNewUser(), ['success' => 'An e-mail has been sent your e-mail address.', 'email-success' => 'email@mailserver.com']);
        $this->builder->setupUserManagerReturnsResult('validateForgottenPasswordForm', $result);
        $this->dispatch('/en_GB/user/forgotten-password', 'POST');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectRegex('/\/user\/forgotten-password/');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('forgotten-password');
        $this->fmtc->assertFlashMessengerHasNamespace('success');
        $this->fmtc->assertFlashMessengerHasMessage('success', 'An e-mail has been sent your e-mail address.');
    }

    /**
     * Ensure that authorised user can access profile page
     */
    public function testAuthorisedUserCanViewProfilePage()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user/profile', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertQuery('div#profile-div');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
    }

    /**
     * Ensure that un-authorised user cannot access profile page and is forwarded to user-auth/login page
     */
    public function testUnAuthorisedUserCannotViewProfilePage()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user/profile', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('profile');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You do not have permission to visit the requested page.');
    }

    /**
     * Ensure that authorised user can get own photo 
     */
    public function testBasicUserIsServedUserPhoto()
    {
        $this->builder->basicAuthorised()->setupUserManagerServePhoto();

        $this->dispatch('/en_GB/user/serve-user-photo/2', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertActionName('serve-user-photo');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
    }

    /**
     * Ensure that un-authorised user cannot get a photo 
     */
    public function testUnuthoriseduserCannotGetPhoto()
    {
        $this->builder->unAuthorised();

        $this->dispatch('/en_GB/user/serve-user-photo/2', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertActionName('serve-user-photo');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'You do not have permission to visit the requested page.');
    }

    /**
     * Test that authorised user can access user/ajax-get-photo-upload-form
     * and JSON is returned
     */
    public function testAutorisedUserCanGetAjaxGetPhotoUploadFormAction()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user/ajax-get-photo-upload-form', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('ajax-get-photo-upload-form');
    }

    /**
     * Test that authorised user can access user/ajax-receive-user-photo-file
     * and JSON is returned
     */
    public function testAjaxReceiveUserPhotoFileAction()
    {
        $this->builder->basicAuthorised();

        $this->dispatch('/en_GB/user/ajax-receive-user-photo-file', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
    }

    public function testApplicationThrowsAnErrorWhenControllersNotArray()
    {
        $config = $this->builder->getService('config');
        $config['controllers'] = 'not array';
        $this->builder->setService('config', $config);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The resources system is based on controller aliases');
        $this->builder->unAuthorised();
    }

    public function testApplicationThrowsAnErrorWhenControllersHaveNoAliases()
    {
        $config = $this->builder->getService('config');
        $config['controllers'] = [];
        $this->builder->setService('config', $config);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The resources system is based on controller aliases');
        $this->builder->unAuthorised();
    }

    public function testApplicationThrowsAnErrorWhenControllersAliasesNotArray()
    {
        $config = $this->builder->getService('config');
        $config['controllers'] = ['aliases' => 'not array'];
        $this->builder->setService('config', $config);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The resources system is based on controller aliases');
        $this->builder->unAuthorised();
    }

    public function testUnauthorisedUserCannotAccessAjaxGetBasicProfileFormAction()
    {

        $this->builder->unAuthorised();
        $this->dispatch('/en_GB/user/ajax-get-basic-profile-form/1', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertRedirectRegex('/\/user-auth\/login/');
        $this->assertRedirectRegex('/redirectUrl=/');
        $this->assertRedirectRegex('/\/user\/ajax-get-basic-profile-form/');
    }

    public function testAjaxGetBasicProfileFormReturnResponseIfUserFound()
    {
        $this->builder->initialiseEntityManagerMock();
        $this->builder->basicAuthorised();
        $this->builder->setupUser();
        $this->dispatch('/en_GB/user/ajax-get-basic-profile-form/1', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('view', $json);
        $this->assertArrayHasKey('success', $json);
        $this->assertEquals(false, $json['success']);
    }

    public function testAjaxGetBasicProfileFormReturnResponseIfUserFoundPostNotValid()
    {
        $this->builder->initialiseEntityManagerMock();
        $this->builder->basicAuthorised();
        $this->builder->setupUser();
        $this->dispatch('/en_GB/user/ajax-get-basic-profile-form/1', 'POST');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('AclUser');
        $this->assertControllerName('user');
        $this->assertControllerClass('UserController');
        $this->assertActionName('ajax-get-basic-profile-form');
        $this->assertResponseHeaderRegex('Content-Type', '/application\/json/');
        $json = json_decode($this->getResponse()->getContent(), true);
        $this->assertArrayHasKey('view', $json);
        $this->assertArrayHasKey('success', $json);
        $this->assertEquals(false, $json['success']);
    }

}
