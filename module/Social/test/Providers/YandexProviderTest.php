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
use Social\Providers\YandexProvider as Provider;

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
class YandexProviderTest extends AbstractHttpControllerTestCase
{

//public static $shared_session = array(  ); 
    protected $providerName = 'yandex';

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
        $this->expectExceptionMessage('Yandex returned an error (1).');
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
        $this->expectExceptionMessage('Yandex returned an error "response was not OK".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 404);
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserWithoutEmails()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yandex returned an error "no emails".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"no_email":"no_email"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserEmailNotArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yandex returned an error "emails not array".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":"email"}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserEmailNotSuitableArray()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yandex returned an error "emails not suitable array".');
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

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserNoName()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yandex returned an error "no name".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":["email"]}');
        $provider->sendClientRequest($callback, $this->builder->getQueryParams());
    }

    public function testProviderThrowsErrorWithInvalidProviderFailedToReturnUserNoId()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Yandex returned an error "no id".');
        $mock = $this->builder->getSocialManagerClientMock();
        $this->builder->mockProviderClient($mock);
        $provider = new Provider($mock);
        $callback = $this->builder->getCallbackUrl($this->providerName);
        $this->builder->setProviderStatusCode(0, 200);
        $this->builder->setProviderJsonBody(1, '{"access_token":"access_token"}');
        $this->builder->setProviderStatusCode(3, 200);
        $this->builder->setProviderJsonBody(2, '{"emails":["email"],"real_name":"name"}');
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
        $this->builder->setProviderJsonBody(2, '{"emails":["email"],"real_name":"name","id":"id"}');
        $result = $provider->sendClientRequest($callback, $this->builder->getQueryParams());
        $this->assertTrue(is_array($result));
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('provider', $result);
    }

}
