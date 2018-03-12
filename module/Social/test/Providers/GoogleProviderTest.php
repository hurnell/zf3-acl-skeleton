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
use Social\Providers\GoogleProvider as Provider;

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
class GoogleProviderTest extends AbstractHttpControllerTestCase
{

//public static $shared_session = array(  ); 
    protected $providerName = 'google';

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

    public function testProviderThrowsErrorWithProviderResponseNoFound()
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
        $this->expectExceptionMessage('Google returned an error (1).');
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
        $this->expectExceptionMessage('Google returned an error "response was not OK".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 404);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUser()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error "no emails".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"no_emails":"no_emails"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserNotArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error "emails not an array".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":"not_array"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserNoDisplayName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error "no display name".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[]}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserNoId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error "no id".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[],"displayName":"displayName"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserEmailNoType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error GoogleProvider::processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[],"displayName":"displayName","id":"id"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserEmailNoValue()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error GoogleProvider::processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[{"type":"type"}],"displayName":"displayName","id":"id"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderReturnUserEmailBadType()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Google returned an error GoogleProvider::processUserProfile.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[{"type":"type","value":"value"}],"displayName":"displayName","id":"id"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderReturnUser()
    {
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":[{"type":"account","value":"value"}],"displayName":"displayName","id":"id"}');

        $result = $provider->sendClientRequest($callback, $this->builder->getQueryParams());
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('provider', $result);
    }

}
