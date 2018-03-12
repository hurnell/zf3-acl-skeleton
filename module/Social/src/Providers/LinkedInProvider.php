<?php

/**
 * Class LinkedInProvider 
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
use Zend\Uri\Http;
use Social\Escaper\Escaper;

/**
 * Class LinkedInProvider 
 * Social Media OAuth2 provider for LINKEDIN
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class LinkedInProvider extends AbstractProvider
{

    /**
     * LINKEDIN's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://www.linkedin.com/oauth/v2/authorization';

    /**
     * The URL that LINKEDIN requires to request an access token
     * 
     * @var string
     */
    protected $requestAccessTokenUrl = 'https://www.linkedin.com/oauth/v2/accessToken';

    /**
     * The URL that LINKEDIN requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://api.linkedin.com/v1/people/~:(id,email-address,formatted-name)';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'linked_in';
    }

    /**
     * Update authorisation parameters
     * In this case just add the single scope
     */
    protected function updateAuthorisationParams()
    {
        $this->authorisationParams['scope'] = 'r_basicprofile r_emailaddress w_share';
    }

    /**
     * Update access parameters
     * in this case just add values for grant_type and scope keys
     * 
     * @param array $queryParams in this case not used
     * @return array (empty)
     */
    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        $this->accessParams['scope'] = 'r_basicprofile r_emailaddress w_share';
        return [];
    }

    /**
     * Handle response after requesting access token from LINKEDIN
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
            throw new \Exception('LinkedIn returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to LINKEDIN to get user profile
     * Using the access token that LINKEDIN returned for previous request
     * Change static escaper for LinkedIn social provider
     * Otherwise the brackets in $requestUserProfileUrl will be URL escaped and the call will fail
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param string $token 
     * @return array corresponding to the user
     */
    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        Http::setEscaper(new Escaper());
        $client->setMethod('GET');
        $client->setUri($this->requestUserProfileUrl);

        $headers = ['Content-Type' => 'application/json', 'x-li-format' => 'json'];
        $client->setHeaders($headers);
        $client->setParameterGet(['format' => 'json', 'oauth2_access_token' => $token]);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that LINKEDIN returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        return [
            'name' => $user->formattedName,
            'email' => $user->emailAddress,
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
            case (!isset($user->emailAddress)):
                $error = '"no email"';
                break;
            case (!isset($user->formattedName)):
                $error = '"no name"';
                break;
            case (!isset($user->id)):
                $error = '"no id"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('LinkedIn returned an error ' . $error . '.');
        }
        return $user;
    }

}
