<?php

/**
 * Class YahooProvider 
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
 * Class YahooProvider 
 * Social Media OAuth2 provider for YAHOO
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class YahooProvider extends AbstractProvider
{

    /**
     * YAHOO's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://api.login.yahoo.com/oauth2/request_auth';

    /**
     * The URL that YAHOO requires to request an access token
     * 
     * @var string
     */
    protected $requestAccessTokenUrl = 'https://api.login.yahoo.com/oauth2/get_token';

    /**
     * The URL that YAHOO requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://social.yahooapis.com/v1/user/me/profile';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'yahoo';
    }

    /**
     * Update authorisation parameters
     * Nothing needed here for YAHOO
     */
    protected function updateAuthorisationParams()
    {
        /**
         * Nothing need to be done here for Yahoo
         */
    }

    /**
     * Update access parameters
     * in this case just add value for grant_type key
     * 
     * @param array $queryParams in this case not used
     * @return array with Authorisation header
     */
    protected function updateAccessParams($queryParams)
    {
        $this->accessParams['grant_type'] = 'authorization_code';
        $header = ['Authorization' => $this->buildFirstAuthorisationHeader()];
        return $header;
    }

    /**
     * Handle response after requesting access token from YAHOO
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param Response $response
     * @return array corresponding to the user
     * @throws \Exception
     */
    protected function handleAccessTokenResponse(Client $client, $response)
    {
        $result = json_decode($response->getBody());
        if (isset($result->access_token) && isset($result->xoauth_yahoo_guid)) {
            $client->resetParameters();
            $headers = ['Authorization' => 'Bearer ' . $result->access_token];
            $client->setHeaders($headers);
            $client->setMethod('GET');
            $client->setUri($this->requestUserProfileUrl);
            $client->setParameterGet(['format' => 'json']);
            $response = $client->send();
            return $this->processUserProfile($response);
        }
        throw new \Exception('Yahoo returned an error (1).');
    }

    /**
     * build the first authorisation header
     * 
     * @return string basic authorisation header
     */
    protected function buildFirstAuthorisationHeader()
    {
        $out = 'Basic ';
        $clientId = $this->accessParams['client_id'];
        $clientSecret = $this->accessParams['client_secret'];
        $out .= base64_encode($clientId . ':' . $clientSecret);
        return $out;
    }

    /**
     * Make new request to YAHOO to get user profile
     * Using the response that YAHOO returned for previous request
     * 
     * @param Response $response 
     * @return array corresponding to the user
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        foreach ($user->profile->emails as $email) {
            if (isset($email->handle) && isset($email->type) && 'HOME' == $email->type) {
                return [
                    'name' => $user->profile->givenName . ' ' . $user->profile->familyName,
                    'email' => $email->handle,
                    'id' => $user->profile->guid,
                    'provider' => $this->providerName
                ];
            }
        }
        throw new \Exception('Yahoo returned an error processUserProfile.');
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
            case (!isset($user->profile)):
                $error = '"no user profile"';
                break;
            case (!isset($user->profile->emails)):
                $error = '"no emails"';
                break;
            case (!is_array($user->profile->emails)):
                $error = '"emails is not an array"';
                break;
            case (!isset($user->profile->givenName)):
                $error = '"no given name"';
                break;
            case (!isset($user->profile->familyName)):
                $error = '"no family name"';
                break;
            case (!isset($user->profile->guid)):
                $error = '"no guid"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Yahoo returned an error ' . $error . '.');
        }
        return $user;
    }

}
