<?php

/**
 * Class LiveProvider 
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
 * Class LiveProvider 
 * Social Media OAuth2 provider for WINDOWS LIVE
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class LiveProvider extends AbstractProvider {

    /**
     * WINDOWS LIVE's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://login.live.com/oauth20_authorize.srf';

    /**
     * The URL that WINDOWS LIVE requires to request an access token
     * 
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://login.live.com/oauth20_token.srf';

    /**
     * The URL that WINDOWS LIVE requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://apis.live.net/v5.0/me';

    /**
     * Set the provider name
     */
    protected function setProviderName() {
        $this->providerName = 'live';
    }

    /**
     * Update authorisation parameters
     * In this case just add the values for the scope and response_type keys
     */
    protected function updateAuthorisationParams() {
        $this->authorisationParams['scope'] = 'wl.basic wl.emails';
        $this->authorisationParams['response_type'] = 'code';
    }

    /**
     * Update access parameters
     * in this case just add value for grant_type key
     * 
     * @param array $queryParams in this case not used
     * @return array (empty)
     */
    protected function updateAccessParams($queryParams) {
        $this->accessParams['grant_type'] = 'authorization_code';
        return [];
    }

    /**
     * Handle response after requesting access token from WINDOWS LIVE
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param Response $response
     * @return array corresponding to the user
     * @throws \Exception
     */
    protected function handleAccessTokenResponse(Client $client, $response) {
        $result = json_decode($response->getBody());
        if (!isset($result->access_token)) {
            throw new \Exception('Windows Live returned an error');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to WINDOWS LIVE to get user profile
     * Using the access token that WINDOWS LIVE returned for previous request
     * 
     * @param Client $client Zend Client that makes the HTTP request
     * @param string $token 
     * @return array corresponding to the user
     */
    public function getUserProfile(Client $client, $token) {
        $client->resetParameters();
        $client->setUri($this->requestUserProfileUrl);
        $client->setMethod('GET');
        $params = [
            'access_token' => $token
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that WINDOWS LIVE returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response) {
        $user = json_decode($response->getBody());
        if (200 != $response->getStatusCode() || !isset($user->id) || !isset($user->emails) || !isset($user->name) || !isset($user->emails->account)
        ) {
            throw new \Exception('Windows Live returned an error');
        }
        return [
            'name' => $user->name,
            'email' => $user->emails->account,
            'id' => $user->id,
            'provider' => $this->providerName
        ];
    }

}
