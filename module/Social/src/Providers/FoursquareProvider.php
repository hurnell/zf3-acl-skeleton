<?php

/**
 * Class FoursquareProvider 
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
 * Class FoursquareProvider 
 * Social Media OAuth2 provider for FOURSQUARE
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class FoursquareProvider extends AbstractProvider
{

    /**
     * FOURSQUARE's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://foursquare.com/oauth2/authenticate';

    /**
     * The URL that FOURSQUARE requires to request an access token
     * 
     * @var string
     */
    protected $requestAccessTokenUrl = 'https://foursquare.com/oauth2/access_token';

    /**
     * The URL that FOURSQUARE requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://api.foursquare.com/v2/users/self';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'foursquare';
    }

    /**
     * Update authorisation parameters
     * In this case nothing needs to be done
     */
    protected function updateAuthorisationParams()
    {
        
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
     * Handle response after requesting access token from FOURSQUARE
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
            throw new \Exception('Foursquare returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to FOURSQUARE to get user profile
     * Using the access token that FOURSQUARE returned for previous request
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param string $token 
     * @return array corresponding to the user
     */
    public function getUserProfile(Client $client, $token)
    {
        $client->resetParameters();
        $client->setMethod('GET');
        $client->setUri($this->requestUserProfileUrl);
        $client->setParameterGet(['oauth_token' => $token, 'v' => '20120609']);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that FOURSQUARE returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {

        $data = $this->checkUserProfile($response);
        return [
            'name' => $data->response->user->firstName . ' ' . $data->response->user->lastName,
            'email' => $data->response->user->contact->email,
            'id' => $data->response->user->id,
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
        $data = json_decode($response->getBody());
        $error = '';
        switch (true) {
            case 200 != $response->getStatusCode():
                $error = '"response was not OK"';
                break;
            case (!isset($data->response->user->contact->email)):
                $error = '"no email"';
                break;
            case (!isset($data->response->user->firstName)):
                $error = '"no first name"';
                break;
            case (!isset($data->response->user->lastName)):
                $error = '"no last name"';
                break;
            case (!isset($data->response->user->id)):
                $error = '"no id"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Foursquare returned an error ' . $error . '.');
        }
        return $data;
    }

}
