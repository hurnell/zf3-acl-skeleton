<?php

/**
 * Class ControllerMockBuilder 
 *
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace SocialTest\Mock;

use AclUserTest\Mock\ControllerMockBuilder;
use Zend\Http\Response;
use Zend\Validator\Csrf;
use Zend\Uri\Http;
use Social\Escaper\Escaper;

/**
 * Contains logic for mocking AclUser\Service\UserManager and 
 * Zend\Authentication\AuthenticationService (as required) and removes and 
 * re-attaches  AccessControlList onDispatch listener
 * 
 * @package     AclUserTest\Mock
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class ProviderMockBuilder extends ControllerMockBuilder
{

    /**
     * Mock of Zend\Http\Client::class 
     * 
     * @var Mock_Client_****
     */
    protected $providerClientMock;

    /**
     * Mock of Zend\Http\Response::class
     * @var Mock_Response_**** 
     */
    protected $mockResponse;

    /**
     * Mock Social\Service\SocialManager::class
     * 
     * @return Mock_SocialManager_***
     */
    public function getSocialManagerproviderClientMock()
    {

        $sessionContainer = $this->getService('social_saved_state');
        $moduleOptions = $this->getService(\Social\Options\ModuleOptions::class);
        $socialAuthManager = $this->getService(\Social\Service\SocialAuthManager::class);
        $constructorArgs = [$moduleOptions, $socialAuthManager, $sessionContainer];
        $socialManagerMock = $this->test->getMockBuilder(\Social\Service\SocialManager::class)->setMethods(['getClient'])->setConstructorArgs($constructorArgs)->getMock();
        return $socialManagerMock;
    }

    /**
     * Mock \Zend\Http\Client::class
     * 
     * @param Mock_SocialManager_*** $socialManagerMock
     */
    public function mockProviderClient($socialManagerMock)
    {
        $this->providerClientMock = $this->test->getMockBuilder(\Zend\Http\Client::class)->setMethods([])->disableOriginalConstructor()->getMock();
        $socialManagerMock->expects($this->test->any())->method('getClient')->willReturn($this->providerClientMock);
        $this->mockResponse = $this->test->getMockBuilder(Response::class)->setMethods([])->disableOriginalConstructor()->getMock();
        $this->providerClientMock->expects($this->test->any())->method('send')->willReturn($this->mockResponse);
    }

    /**
     * Tell Mock_Response_**** to return $statusCode when getStatusCode is called for the $index th time
     * 
     * @param integer $index when (integer) getStatusCode is called
     * @param integer $statusCode
     */
    public function setProviderStatusCode($index, $statusCode)
    {
        $this->mockResponse->expects($this->test->at($index))->method('getStatusCode')->willReturn($statusCode);
    }

    /**
     * Tell Mock_Response_****  to return $jsonBody when getBody is called for the $index th time
     * 
     * @param integer $index when (integer) getBody is called
     * @param string $jsonBody
     */
    public function setProviderJsonBody($index, $jsonBody = null)
    {
        $this->mockResponse->expects($this->test->at($index))->method('getBody')->willReturn($jsonBody);
    }

    /**
     * Get callback URL for given provider
     * 
     * @param string $providerName social provider name
     * @return string
     */
    public function getCallbackUrl($providerName)
    {
        return 'http://skeleton.hurnell.com/en_GB/social/redirected/' . $providerName;
    }

    /**
     * Get (real|new) Zend\Http\Client
     * 
     * @return \Zend\Http\Client
     */
    public function getClient($isTwitter = false)
    {
        if ($isTwitter) {
            Http::setEscaper(new Escaper());
        }
        $client = new \Zend\Http\Client();
        $options = array(
            'adapter' => 'Zend\Http\Client\Adapter\Curl',
            'curloptions' => array(CURLOPT_FOLLOWLOCATION => true),
        );
        $client->setOptions($options);
        return $client;
    }

    /**
     * Get dummy query parameters (including valid CSRF hash)
     * 
     * @param boolean $valid
     * @return array
     */
    public function getQueryParams($valid = true)
    {
        $hash = 'dummy_state';
        if ($valid) {
            $csrf = new Csrf();
            $hash = $csrf->getHash(false);
        }
        return [
            'code' => 'dummy_code',
            'state' => $hash
        ];
    }

    /**
     * Get parameters for twitter
     * 
     * @return array
     */
    public function getTwitterParams()
    {
        return [
            'oauth_token' => 'oauth_token',
            'oauth_verifier' => 'oauth_verifier',
            'extra' => '',
        ];
    }

}
