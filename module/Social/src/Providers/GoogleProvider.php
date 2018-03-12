<?php

/**
 * Class GoogleProvider 
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
 * Class GoogleProvider 
 * Social Media OAuth2 provider for GOOGLE
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class GoogleProvider extends AbstractProvider
{

    /**
     * GOOGLE's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * The URL that GOOGLE requires to request an access token
     * 
     * @var string
     */
    protected $requestAccessTokenUrl = 'https://www.googleapis.com/oauth2/v4/token';

    /**
     * The URL that GOOGLE requires to request the user's profile
     * 
     * @var string
     */
    protected $requestUserProfileUrl = 'https://www.googleapis.com/plus/v1/people/me';

    /**
     * Scopes that GOOGLE requires to access user's e-mail etc
     * 
     * @var array
     */
    protected $scopes = [
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/userinfo.email',
    ];

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'google';
    }

    /**
     * Update authorisation parameters
     * In this case just add the concatenated scopes
     */
    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = implode(' ', $this->scopes);
    }

    /**
     * Update access parameters
     * in this case just add value for grant_type key
     * 
     * @param array $queryParams in this case not used
     * @return array (empty)
     */
    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        return [];
    }

    /**
     * Handle response after requesting access token from GOOGLE
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param Response $response
     * @return array corresponding to the user
     * @throws \Exception
     */
    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $body = $response->getBody();
        $result = json_decode($body);
        if (!isset($result->access_token)) {
            throw new \Exception('Google returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to GOOGLE to get user profile
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
            'alt' => 'json'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserprofile($response);
    }

    /**
     * Process the response that GOOGLE returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        foreach ($user->emails as $email) {
            if (isset($email->type) && isset($email->value) && 'account' == $email->type) {
                return [
                    'name' => $user->displayName,
                    'email' => $email->value,
                    'id' => $user->id,
                    'provider' => $this->providerName
                ];
            }
        }
        throw new \Exception('Google returned an error GoogleProvider::processUserProfile.');
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
            case (!isset($user->emails)):
                $error = '"no emails"';
                break;
            case (!is_array($user->emails)):
                $error = '"emails not an array"';
                break;
            case (!isset($user->displayName)):
                $error = '"no display name"';
                break;
            case (!isset($user->id)):
                $error = '"no id"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Google returned an error ' . $error . '.');
        }
        return $user;
    }

}
