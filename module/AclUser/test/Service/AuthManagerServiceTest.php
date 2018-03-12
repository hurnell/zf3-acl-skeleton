<?php

/**
 * Class AuthManagerServiceTest 
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Service;

use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use AclUserTest\Mock\ServiceMockBuilder;
use Zend\Authentication\Result;

/**
 * Test various aspects of AclUser\Service\AuthManager
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class AuthManagerServiceTest extends AbstractHttpControllerTestCase
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
     * @param boolean $withSessionManager whether to mock \Zend\Session\SessionManager before creation
     * @return \AclUser\Service\UserManager
     */
    protected function getAuthManagerService($withSessionManager = false)
    {
        return $this->builder->buildServiceWithOptionalSessionManager(\AclUser\Service\AuthManager::class, $withSessionManager);
    }

    public function testAuthManagerServiceLoginThowsExceptionWhenAlreadyLoggedIn()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Already logged in', 'Method should throw error with message "Already logged in"');
        $this->builder->mockAuthService(['getIdentity' => 1]);
        $service = $this->getAuthManagerService();
        $service->login('', '');
        $this->assertNotTrue(true, 'You should not be getting here');
    }

    public function testAuthManagerServiceLoginFailsWithInvalidCredentials()
    {
        $mockedAdapter = $this->builder->getMocked(
                \AclUser\Service\AuthAdapter::class,
                ['setEmail' => null, 'setPassword' => null]);
        $result = new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['error' => 'This e-mail address does not have an account.']);

        $this->builder->mockAuthService([
            'getIdentity' => null,
            'getAdapter' => $mockedAdapter,
            'authenticate' => $result
        ]);
        $service = $this->getAuthManagerService();
        $outcome = $service->login('invalid@mailserver.com', 'invalid_password');
        $this->assertTrue($outcome instanceof Result, 'AuthManager service login should return instance of Result here');
        $this->assertNotTrue($outcome->isValid(), 'AuthManager service login should return invalid result here');
        $messages = $result->getMessages();

        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertTrue($messages['error'] == 'This e-mail address does not have an account.');
    }

    public function testAuthManagerServiceLoginFailsForRetiredUser()
    {
        $mockedAdapter = $this->builder->getMocked(
                \AclUser\Service\AuthAdapter::class,
                ['setEmail' => null, 'setPassword' => null]);
        $result = new Result(
                Result::FAILURE_IDENTITY_NOT_FOUND, null, ['error' => 'The account for this e-mail address has been suspended.']);

        $this->builder->mockAuthService([
            'getIdentity' => null,
            'getAdapter' => $mockedAdapter,
            'authenticate' => $result
        ]);
        $service = $this->getAuthManagerService();
        $outcome = $service->login('invalid@mailserver.com', 'invalid_password');
        $this->assertTrue($outcome instanceof Result, 'AuthManager service should return instance of Result');
        $this->assertNotTrue($outcome->isValid(), 'AuthManager service login should return invalid result here');
        $messages = $result->getMessages();

        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertTrue($messages['error'] == 'The account for this e-mail address has been suspended.', '$result->getMessages() should return array with one value given for error');
    }

    public function testAuthManagerServiceLoginSuccceedsWithoutRememberMe()
    {
        $mockedAdapter = $this->builder->getMocked(
                \AclUser\Service\AuthAdapter::class,
                ['setEmail' => null, 'setPassword' => null]);
        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);

        $this->builder->mockAuthService([
            'getIdentity' => null,
            'getAdapter' => $mockedAdapter,
            'authenticate' => $result
        ]);
        $service = $this->getAuthManagerService();
        $outcome = $service->login('invalid@mailserver.com', 'invalid_password');
        $this->assertTrue($outcome instanceof Result, 'AuthManager service should return instance of Result');
        $this->assertTrue($outcome->isValid(), 'AuthManager service login should return valid result here');
        $messages = $result->getMessages();

        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('success', $messages, '$result->getMessages() should return array with one key that is success');
        $this->assertTrue($messages['success'] == 'You have been successfully logged in.', '$result->getMessages() should return array with one value given for success');
    }

    public function testAuthManagerServiceLoginSuccceedsWithRememberMe()
    {
        $mockedAdapter = $this->builder->getMocked(
                \AclUser\Service\AuthAdapter::class,
                ['setEmail' => null, 'setPassword' => null]);
        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);

        $this->builder->mockAuthService([
            'getIdentity' => null,
            'getAdapter' => $mockedAdapter,
            'authenticate' => $result
        ]);
        $service = $this->getAuthManagerService(true);
        $outcome = $service->login('invalid@mailserver.com', 'invalid_password', true);
        $this->assertTrue($outcome instanceof Result, 'AuthManager service should return instance of Result');
        $this->assertTrue($outcome->isValid(), 'AuthManager service login should return valid result here');
        $messages = $result->getMessages();

        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('success', $messages, '$result->getMessages() should return array with one key that is success');
        $this->assertTrue($messages['success'] == 'You have been successfully logged in.', '$result->getMessages() should return array with one value given for success');
    }

    public function testAuthManagerServiceLogoutThowsExceptionWhenNotLoggedIn()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The user is not logged in');
        $this->builder->mockAuthService(['getIdentity' => null]);
        $service = $this->getAuthManagerService();
        $service->logout();
        $this->assertNotTrue(true, 'You should not be getting here');
    }

    public function testAuthManagerServiceLoginUserExecutes()
    {
        $mock = $this->builder->getMocked(\Zend\Authentication\Storage\Session::class, ['write' => null]);
        $this->builder->mockAuthService(['hasIdentity' => true, 'clearIdentity' => null, 'getStorage' => $mock]);
        $service = $this->getAuthManagerService();
        $user = $this->builder->getNewUser();
        $result = $service->loginUser($user);
        $this->assertTrue($result == null, 'loginUser should return null');
    }

    public function testAuthManagerServiceLogoutSucceeds()
    {
        $this->builder->mockAuthService(['getIdentity' => 1, 'clearIdentity' => null]);
        $service = $this->getAuthManagerService();
        $null = $service->logout();
        $this->assertNull($null, 'Method call should have returned null');
    }

    public function testAuthManagerServiceCanFailWithInvalidCredentials()
    {
        $params = [];
        $form = $this->builder->mockForm(\AclUser\Form\LoginForm::class, false, $params);
        $service = $this->getAuthManagerService();
        $result = $service->validateLoginForm($form, $params);
        $this->assertNotTrue($result->isValid(), 'validateLoginForm should return invalid result');
        $messages = $result->getMessages();

        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertTrue($messages['error'] == 'Form is not valid.');
    }

    public function testAuthManagerServiceCanFailWithValidCredentials()
    {
        $params = ['email' => 'valid@mailserver.com', 'password' => 'valid_password', 'remember_me' => false];
        $form = $this->builder->mockForm(\AclUser\Form\LoginForm::class, true, $params);

        $mockedAdapter = $this->builder->getMocked(
                \AclUser\Service\AuthAdapter::class,
                ['setEmail' => null, 'setPassword' => null]);
        $result = new Result(
                Result::SUCCESS, 1, ['success' => 'You have been successfully logged in.']);

        $this->builder->mockAuthService([
            'getIdentity' => null,
            'getAdapter' => $mockedAdapter,
            'authenticate' => $result
        ]);
        $service = $this->getAuthManagerService();
        $outcome = $service->validateLoginForm($form, $params);
        $this->assertTrue($outcome->isValid(), 'validateLoginForm should return valid result');
        $messages = $outcome->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('success', $messages, '$result->getMessages() should return array with one key that is success');
        $this->assertTrue($messages['success'] == 'You have been successfully logged in.', '$result->getMessages() should return array with one value given for success');
    }

}
