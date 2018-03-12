<?php

/**
 * Class SocialControllerTest 
 *
 * @package     AclUserTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace SocialTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ControllerMockBuilder;

/**
 * Test various aspects of AclUser\Controller\AuthController
 */
class SocialControllerTest extends AbstractHttpControllerTestCase
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

    // START LOGIN

    public function testUnauthorisedUserCanAccessStartLoginPage()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        foreach ($providers as $provider) {
            $this->probeStartSocialLogins($provider, 'start-login');
            break;
        }
        if (count($providers) == 0) {
            $this->assertTrue(true, 'There were no social providers');
        }
    }

    public function testUnauthorisedUserGetsToStartLoginPageWithUnknownProvider()
    {
        $this->dispatch('/en_GB/social/start-login/unknown_provider', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName('start-login');
        $this->assertQuery('ul.alert');
        $this->assertQuery('ul.alert-danger');
    }

    // START REGISTRATION

    public function testUnauthorisedUserCanAccessStartRegistrationPage()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        foreach ($providers as $provider) {
            $this->probeStartSocialLogins($provider, 'start-registration');
            break;
        }
        if (count($providers) == 0) {
            $this->assertTrue(true, 'There were no social providers');
        }
    }

    public function testUnauthorisedUserGetsToStartRegistrationPageWithUnknownProvider()
    {
        $this->dispatch('/en_GB/social/start-registration/unknown_provider', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName('start-registration');
        $this->assertQuery('ul.alert');
        $this->assertQuery('ul.alert-danger');
    }

    // START LOGIN OR REGISTRATION

    public function testUnauthorisedUserCanAccessStartLoginOrRegistrationPage()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        foreach ($providers as $provider) {
            $this->probeStartSocialLogins($provider, 'start-login-or-registration');
            break;
        }
        if (count($providers) == 0) {
            $this->assertTrue(true, 'There were no social providers');
        }
    }

    public function testUnauthorisedUserGetsToStartLoginOrRegistrationPageWithUnknownProvider()
    {
        $this->dispatch('/en_GB/social/start-login-or-registration/unknown_provider', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName('start-login-or-registration');
        $this->assertQuery('ul.alert');
        $this->assertQuery('ul.alert-danger');
    }

    protected function probeStartSocialLogins($provider, $action)
    {
        $this->reset();
        $this->dispatch('/en_GB/social/' . $action . '/' . $provider, 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName($action);
        $this->assertRedirectRegex('/' . $provider . '/');
    }

    // FILED_LOGIN

    public function testUnauthorisedCanAccessFailedLogin()
    {
        $this->builder->unAuthorised();
        $this->dispatch('/en_GB/social/failed-login', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName('failed-login');
    }

    // REDIRECTED

    public function testUnknownProviderRedirectsToFailedLogin()
    {
        $this->builder->unAuthorised();
        $this->builder->mockSessionContainer(['nl_NL']);
        $this->dispatch('/en_GB/social/redirected/unknown_provider', 'GET');
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Social');
        $this->assertControllerName('social');
        $this->assertControllerClass('SocialController');
        $this->assertActionName('redirected');
        $this->assertRedirectRegex('/social\/failed-login/');
        $this->fmtc->assertFlashMessengerHasNamespace('error');
        $this->fmtc->assertFlashMessengerHasMessage('error', 'An unknown error occured.');
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndFailLoginWithoutUser()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(false);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'login']);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/social\/failed-login/');
            $this->fmtc->assertFlashMessengerHasMessage('error', 'This e-mail address does not have an account.');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndFailWithSuspendedUserLogin()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true, false, ['setStatus' => false]);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'login']);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/index\/index/');

            $this->fmtc->assertFlashMessengerHasNamespace('error');
            $this->fmtc->assertFlashMessengerHasMessage('error', 'The account for this e-mail address has been suspended.');
            $this->fmtc->assertFlashMessengerHasNamespace('email');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndFailRegistationWithEmailAlreadyInUse()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'registration']);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/social\/failed-login/');

            $this->fmtc->assertFlashMessengerHasNamespace('error');
            $this->fmtc->assertFlashMessengerHasMessage('error', 'This e-mail address already has an account.');
            $this->fmtc->assertFlashMessengerHasNamespace('email');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndSucceedRegistation()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(false, false, [], 0);
            $this->builder->mockOtherRepoFinder('role', 1);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'registration']);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/index\/index/');

            $this->fmtc->assertFlashMessengerHasNamespace('success');
            $this->fmtc->assertFlashMessengerHasMessage('success', 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndSucceedLoginOrRegistation()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(false, false, [], 0);
            $this->builder->mockOtherRepoFinder('role', 1);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'loginregistration']);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/index\/index/');

            $this->fmtc->assertFlashMessengerHasNamespace('success');
            $this->fmtc->assertFlashMessengerHasMessage('success', 'A message has been sent to your e-mail address. Please follow the link to confirm your identity and activate your account.');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndSucceedUserLogin()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true, false);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'login']);

            $mock = $this->builder->getMocked(\Zend\Authentication\Storage\Session::class, ['write' => null]);
            $this->builder->mockAuthService(['hasIdentity' => true, 'clearIdentity' => null, 'getStorage' => $mock]);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/index\/index/');
            $this->fmtc->assertFlashMessengerHasNamespace('success');
            $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been successfully logged in.');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedAndSucceedUserLoginOrRegistration()
    {
        $this->builder->unAuthorised();
        $providers = $this->builder->getEnabledSocialProviders();
        $length = count($providers);
        if ($length > 0) {
            $provider = $providers [rand(0, $length - 1)];
            $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true, false);
            $sessionContainer = $this->builder->mockSessionContainer(['nl_NL', 'loginregistration']);

            $mock = $this->builder->getMocked(\Zend\Authentication\Storage\Session::class, ['write' => null]);
            $this->builder->mockAuthService(['hasIdentity' => true, 'clearIdentity' => null, 'getStorage' => $mock]);
            $this->builder->mockClient($provider, $sessionContainer);
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
            $this->assertResponseStatusCode(302);
            $this->assertRedirectRegex('/index\/index/');
            $this->fmtc->assertFlashMessengerHasNamespace('success');
            $this->fmtc->assertFlashMessengerHasMessage('success', 'You have been successfully logged in.');
        }
    }

    public function testUnauthorisedCanAccessSocialRedirectedWillFailWithoutGetClientMockTwitter()
    {
        $this->builder->unAuthorised();
        $provider = 'twitter';
        $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true, false);
        $sessionContainer = $this->builder->mockSessionContainer(['nl_NL']);

        $mock = $this->builder->getMocked(\Zend\Authentication\Storage\Session::class, ['write' => null]);
        $this->builder->mockAuthService(['hasIdentity' => true, 'clearIdentity' => null, 'getStorage' => $mock]);
        $this->builder->mockClient($provider, $sessionContainer, false);
        try {
            $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET');
        } catch (Exception $e) {
            $this->assertEquals('Twitter returned an error (1).', $e->getMessage());
        }
        $this->assertResponseStatusCode(500);
    }

    public function testUnauthorisedCanAccessSocialRedirectedWillFailWithoutGetClientMockGoogle()
    {
        $this->builder->unAuthorised();
        $provider = 'google';
        $this->builder->initialiseEntityManagerMock()->setEntityManagerExpectsGetRepository()->setupUser(true, false);
        $sessionContainer = $this->builder->mockSessionContainer(['nl_NL']);

        $mock = $this->builder->getMocked(\Zend\Authentication\Storage\Session::class, ['write' => null]);
        $this->builder->mockAuthService(['hasIdentity' => true, 'clearIdentity' => null, 'getStorage' => $mock]);
        $this->builder->mockClient($provider, $sessionContainer, false);
        $this->dispatch('/en_GB/social/redirected/' . $provider, 'GET', ['code' => 'code', 'state' => 'state']);
        $this->assertResponseStatusCode(500);
        $this->assertQueryContentContains('pre', 'AbstractProvider::sendClientRequest failed to return valid response.');
    }

}
