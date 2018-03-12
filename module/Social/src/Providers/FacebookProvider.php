<?php

/**
 * Class FacebookProvider 
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Providers;

use Social\Providers\AbstractProvider\AbstractProvider;
use Zend\Http\Client;

/**
 * Class FacebookProvider 
 * Social Media OAuth2 provider for FACEBOOK
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class FacebookProvider extends AbstractProvider
{

    /**
     * FACEBOOK's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://www.facebook.com/v2.9/dialog/oauth';

    /**
     * The URL that FACEBOOK requires to request an access token
     * 
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://graph.facebook.com/v2.9/oauth/access_token';

    /**
     * The URL that FACEBOOK requires to request the user's profile
     * @var type 
     */
    protected $requestUserProfileUrl = 'https://graph.facebook.com/me';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'facebook';
    }

    /**
     * Update authorisation parameters
     * just add scope and display keys in this case
     */
    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'email';
        $this->authorisationParams['display'] = 'page';
    }

    /**
     * Update access parameters
     * nothing needs to be done in this case
     * 
     * @param array $queryParams in this case not used
     * @return array (empty)
     */
    protected function updateAccessParams($queryParams)
    {
        return [];
    }

    /**
     * Handle response after requesting access token from FACEBOOK
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param Response $response
     * @return array corresponding to the user
     * @throws \Exception
     */
    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('Facebook returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to FACEBOOK to get user profile
     * Using the access token that GOOGLE returned for previous request
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param string $token 
     * @return array corresponding to the user
     */
    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setUri($this->requestUserProfileUrl);
        $client->setMethod('GET');
        $params = [
            'access_token' => $token,
            'fields' => 'id,name,email'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that FACEBOOK returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        $result = [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
        return $result;
    }

    /**
     * CHeck that the returned response has the required parameters
     * 
     * @param Zend\Http\Response $response
     * @return JSON object
     * @throws \Exception when returned object does not conform to requirements
     */
    protected function checkUserProfile($response)
    {
        $user = json_decode($response->getBody());
        $error = '';
        switch (true) {
            case 200 != $response->getStatusCode():
                $error = '"response was not OK"';
                break;
            case (!isset($user->email)):
                $error = '"no email"';
                break;
            case (!isset($user->name)):
                $error = '"no name"';
                break;
            case (!isset($user->id)):
                $error = '"no id"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Facebook returned an error ' . $error . '.');
        }
        return $user;
    }

}
