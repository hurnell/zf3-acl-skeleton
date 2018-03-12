<?php

/**
 * Class AuthAdapterServiceTest 
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
use Zend\Authentication\Result;

/**
 * Test various aspects of AclUser\Service\AuthAdapter
 *
 * @package     AclUserTest\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class AuthAdapterServiceTest extends AbstractHttpControllerTestCase
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
     * @return \AclUser\Service\UserManager
     */
    protected function getAuthAdapterService()
    {
        return $this->builder->buildServiceWithOptionalSessionManager(\AclUser\Service\AuthAdapter::class);
    }

    public function testAuthAdapterServiceCanFailAuthenticateWithoutUser()
    {
        $this->builder->setupUser(false);
        $service = $this->getAuthAdapterService();
        $email = 'unknown@mailserver.com';
        $service->setEmail($email);
        $result = $service->authenticate();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertArrayHasKey('email', $messages, '$result->getMessages() should return array with one key that is email');
        $error = 'This e-mail address does not have an account.';
        $this->assertTrue($messages['error'] == $error, '$result->getMessages() should error: ' . $messages['error'] . ' AND NOT: ' . $error);
        $this->assertTrue($messages['email'] == $email, '$result->getMessages() should return email: ' . $messages['email'] . ' AND NOT: ' . $email);
    }

    public function testAuthAdapterServiceCanFailAuthenticateWhenUserIsSuspended()
    {
        $this->builder->setupUser(true, true, ['setStatus' => false]);
        $service = $this->getAuthAdapterService();
        $email = 'unknown@mailserver.com';
        $service->setEmail($email);
        $result = $service->authenticate();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertArrayHasKey('email', $messages, '$result->getMessages() should return array with one key that is email');
        $error = 'The account for this e-mail address has been suspended.';
        $this->assertTrue($messages['error'] == $error, '$result->getMessages() should error: ' . $messages['error'] . ' AND NOT: ' . $error);
        $this->assertTrue($messages['email'] == $email, '$result->getMessages() should return email: ' . $messages['email'] . ' AND NOT: ' . $email);
    }

    public function testAuthAdapterServiceCanFailAuthenticateWhenPasswordIsWrong()
    {
        $this->builder->setupUser(true, true);
        $service = $this->getAuthAdapterService();
        $service->setPassword('wrong_password');
        $email = 'admin@application.com';
        $service->setEmail($email);
        $result = $service->authenticate();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $this->assertArrayHasKey('email', $messages, '$result->getMessages() should return array with one key that is email');
        $error = 'Incorrect password for this e-mail address.';
        $this->assertTrue($messages['error'] == $error, '$result->getMessages() should error: ' . $messages['error'] . ' AND NOT: ' . $error);
        $this->assertTrue($messages['email'] == $email, '$result->getMessages() should return email: ' . $messages['email'] . ' not ' . $email);
    }

    public function testAuthAdapterServiceCanSuccessfullyAuthenticateWhenPasswordIsCorrect()
    {
        $this->builder->setupUser(true, true);
        $service = $this->getAuthAdapterService();
        $service->setPassword('Secret#p@ss');
        $email = 'admin@application.com';
        $service->setEmail($email);
        $result = $service->authenticate();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('success', $messages, '$result->getMessages() should return array with one key that is success');
        $welcome = 'You have been successfully logged in.';
        $this->assertTrue($messages['success'] == $welcome, '$result->getMessages() should error: ' . $messages['success'] . ' AND NOT: ' . $welcome);
    }

    public function testAuthAdapterServiceCannotCompleteSocialLoginWithoutUser()
    {
        $this->builder->setupUser(false);
        $service = $this->getAuthAdapterService();
        $result = $service->adapterCompleteSocialLogin();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $error = 'This e-mail address does not have an account.';
        $this->assertTrue($messages['error'] == $error, '$result->getMessages() should error: ' . $messages['error'] . ' AND NOT: ' . $error);
    }

    public function testAuthAdapterServiceCannotCompleteSocialLoginWhenUserIsSuspended()
    {
        $this->builder->setupUser(true, true, ['setStatus' => false]);
        $service = $this->getAuthAdapterService();
        $result = $service->adapterCompleteSocialLogin();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('error', $messages, '$result->getMessages() should return array with one key that is error');
        $error = 'The account for this e-mail address has been suspended.';
        $this->assertTrue($messages['error'] == $error, '$result->getMessages() should error: ' . $messages['error'] . ' AND NOT: ' . $error);
    }

    public function testAuthAdapterServiceCanSuccessCompleteSocialLoginWhen()
    {
        $this->builder->setupUser(true, true);
        $service = $this->getAuthAdapterService();
        $service->setPassword('Secret#p@ss');
        $result = $service->adapterCompleteSocialLogin();
        $this->assertTrue($result instanceof Result, 'AuthAdapter::authenticate should return instance of Result');
        $messages = $result->getMessages();
        $this->assertTrue(is_array($messages), '$result->getMessages() should return an array');
        $this->assertArrayHasKey('success', $messages, '$result->getMessages() should return array with one key that is success');
        $welcome = 'You have been successfully logged in.';
        $this->assertTrue($messages['success'] == $welcome, '$result->getMessages() should error: ' . $messages['success'] . ' AND NOT: ' . $welcome);
    }

}
