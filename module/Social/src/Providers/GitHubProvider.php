<?php

/**
 * Class GitHubProvider 
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
 * Class GitHubProvider 
 * Social Media OAuth2 provider for GitHub
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class GitHubProvider extends AbstractProvider
{

    /**
     * GITHUB's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://github.com/login/oauth/authorize';

    /**
     * The URL that GITHUB requires to request an access token
     * 
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://github.com/login/oauth/access_token';

    /**
     * The URL that GITHUB requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://api.github.com/user';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'git_hub';
    }

    /**
     * Update authorisation parameters
     * In this case just add the scope
     */
    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'user:email';
    }

    /**
     * Update access parameters
     * in this case just add value for state key
     * 
     * @param array $queryParams in this case not used
     * @return array 
     */
    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['state'] = $queryParams['state'];
        return ['Accept' => 'application/json'];
    }

    /**
     * Handle response after requesting access token from GITHUB
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
            throw new \Exception('GitHub returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to GITHUB to get user profile
     * Using the access token that GITHUB returned for previous request
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
        $headers = ['Authorization' => 'token ' . $token];
        $client->setHeaders($headers);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that GITHUB returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        return [
            'name' => $user->name,
            'email' => $user->email,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
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
            throw new \Exception('GitHub returned an error ' . $error . '.');
        }
        return $user;
    }

}
