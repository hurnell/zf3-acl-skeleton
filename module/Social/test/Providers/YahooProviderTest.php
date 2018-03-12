<?php

/**
 * Class IndexControllerTest 
 *
 * @package     ApplicationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace SocialTest\Providers;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use SocialTest\Mock\ProviderMockBuilder;
use Social\Providers\YahooProvider as Provider;

/**
 * Test various aspects of Application\Controller\IndexController
 * 
 * @package     ApplicationTest\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 * 
 */
class YahooProviderTest extends AbstractHttpControllerTestCase
{

//public static $shared_session = array(  ); 
    protected $providerName = 'yahoo';

    /**
     * Set up the unit test
     */
    public function setUp()
    {
        $this->setApplicationConfig(ProviderMockBuilder::getConfig());
        parent::setUp();
        $this->getApplicationServiceLocator()->setAllowOverride(true);
        $this->builder = new ProviderMockBuilder($this);
    }

    public function testThatProviderCanBeContacted()
    {
        $mock = $this->builder->getSocialManagerClientMock();
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $url = $provider->getRedirectRoute($callback);
        $client = $this->builder->getClient();
        $client->setUri($url);
        $response = $client->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(true);
    }

    public function testProviderThrowsErrorWithInvalidParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The social provider returned invalid parameters.');
        $mock = $this->builder->getSocialManagerClientMock();
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams(false));
    }

    public function testProviderThrowsErrorWithInvalidProviderResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('AbstractProvider::sendClientRequest failed to return valid response.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 404);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderResponseNoAccessToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error (1).');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(0, '{"no_access_token":"no_access_token"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedSecondResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "response was not OK".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 404);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutProfile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "no user profile".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"no_profile":"no_profile"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutEmails()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "no emails".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"no_emails":"no_emails"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutEmailsNOtArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "emails is not an array".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":"not_array"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutGivenName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "no given name".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[]}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutFamilyName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "no family name".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[],"givenName":"givenName"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error "no guid".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[], "givenName":"givenName", "familyName":"familyName" }}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserEmailHandle()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[],"givenName":"givenName","familyName":"familyName", "guid":"guid"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserEmailType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[{"handle":"email"}],"givenName":"givenName","familyName":"familyName", "guid":"guid"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserEmailTypeNotHome()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yahoo returned an error processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[{"handle":"email","type":"not HOME"}],"givenName":"givenName","familyName":"familyName", "guid":"guid"}}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderReturnUser()
    {
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token","xoauth_yahoo_guid":"xoauth_yahoo_guid"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"profile":{"emails":[{"handle":"email","type":"HOME"}],"givenName":"givenName","familyName":"familyName", "guid":"guid"}}');

        $result = $provider->sendClientRequest($callback, $this->builder->getQueryParams());
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('provider', $result);
    }

    // */
}
