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
use Social\Providers\TwitterProvider as Provider;
use SocialTest\Http\Escaper;

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
class TwitterProviderTest extends AbstractHttpControllerTestCase
{

//public static $shared_session = array(  ); 
    protected $providerName = 'twitter';

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
        $nonMock = $this->getApplicationServiceLocator()->get(\Social\Service\SocialManager::class);
        $provider = new Provider($nonMock);

        $callback = $this->builder->getCallbackUrl($this->providerName);
        $url = $provider->getRedirectRoute($callback);
        $client = $this->builder->getClient();
        $client->setUri($url);
        $response = $client->send();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue(true);
    }

    public function testThatTwitterProviderCanBeContacted()
    {
        $nonMock = $this->getApplicationServiceLocator()->get(\Social\Service\SocialManager::class);

        $callback = $this->builder->getCallbackUrl($this->providerName);
        $client = $this->builder->getClient(true);
        $client->setUri('https://api.linkedin.com/v1/people/~:(id,email-address,formatted-name)');
        $response = $client->send();
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertTrue(true);
    }

    public function testThatProviderCannotBeContacted()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter returned an error');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $provider->getRedirectRoute('');
    }

    public function testProviderThrowsErrorWithInvalidParams()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter returned an error (1).');
        $mock = $this->builder->getSocialManagerClientMock();
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams(false));
    }

    public function testProviderThrowsErrorWithInvalidProviderResponse()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter returned an error (1).');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 404);
        $provider->sendClientRequest($callback, $this->builder->getTwitterParams());
    }

    public function testProviderThrowsErrorWithInvalidNoOauthToken()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter returned an error "oauth_token not in array".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(1, 200);
        $provider->sendClientRequest($callback, $this->builder->getTwitterParams());
    }

    public function testProviderThrowsErrorWithInvalidNoOauthSecret()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter returned an error "oauth_token_secret not in array".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(1, 200);
        $this->builder->setProviderJsonBody(2, 'oauth_token=oauth_token');
        $provider->sendClientRequest($callback, $this->builder->getTwitterParams());
    }

    public function testProviderThrowsErrorWithInvalidNoOauthSecretNoCredentials()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Twitter could not get valid credentials.');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(1, 200);
        $this->builder->setProviderJsonBody(2, 'oauth_token=oauth_token&oauth_token_secret=oauth_token_secret&continue=continue');
        $provider->sendClientRequest($callback, $this->builder->getTwitterParams());
    }

    public function testProviderThrowsErrorWithInvalidNoOauthSecretw()
    {
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(1, 200);
        $this->builder->setProviderJsonBody(2, 'oauth_token=oauth_token&oauth_token_secret=oauth_token_secret');
        $this->builder->setProviderStatusCode(4, 200);
        $this->builder->setProviderJsonBody(5, '{"name":"name","email":"email","id":"id"}');
        $result = $provider->sendClientRequest($callback, $this->builder->getTwitterParams());
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('provider', $result);
    }

}
