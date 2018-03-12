<?php

/**
 * Class YandexProvider 
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
 * Social Media OAuth2 provider for YANDEX
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class YandexProvider extends AbstractProvider
{

    /**
     * YANDEX's base authorisation URL
     * 
     * @var string 
     */
    protected $baseAuthorisationUrl = 'https://oauth.yandex.com/authorize';

    /**
     * The URL that YANDEX requires to request an access token
     * 
     * @var string 
     */
    protected $requestAccessTokenUrl = 'https://oauth.yandex.com/token';

    /**
     * The URL that YANDEX requires to request the user's profile
     * 
     * @var string 
     */
    protected $requestUserProfileUrl = 'https://login.yandex.ru/info';

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'yandex';
    }

    /**
     * Update authorisation parameters
     * do nothing in this case
     */
    protected function updateAuthorisationParams()
    {
        
    }

    /**
     * Update access parameters
     * update grant_type in this case
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
     * Handle response after requesting access token from YANDEX
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
            throw new \Exception('Yandex returned an error (1).');
        }
        return $this->getUserProfile($client, $result->access_token);
    }

    /**
     * Make new request to YANDEX to get user profile
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
        $header = ['Authorization' => 'Bearer ' . $token];
        $client->setHeaders($header);
        $params = [
            'format' => 'json'
        ];
        $client->setParameterGet($params);
        $response = $client->send();
        return $this->processUserProfile($response);
    }

    /**
     * Process the response that YANDEX returned to the getUserProfile request
     * 
     * @param Response $response 
     * @return array array containing user profile
     * @throws \Exception if the response does not contain user profile
     */
    protected function processUserProfile($response)
    {
        $user = $this->checkUserProfile($response);
        return [
            'name' => $user->real_name,
            'email' => $user->emails[0],
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
        /**
         * 
          $user = json_decode($response->getBody());
          //var_dump($user);
          //die(__METHOD__);
          if (200 != $response->getStatusCode() || !isset($user->id) || !isset($user->emails) || !is_array($user->emails) || !count($user->emails) > 0) {
          throw new \Exception('Yandex returned an error');
          }
         */
        $user = json_decode($response->getBody());
        $error = '';
        switch (true) {
            case 200 != $response->getStatusCode():
                $error = '"response was not OK"';
                break;
            case (!isset($user->emails)):
                $error = '"no emails"';
                break;
            case (!is_array($user->emails) ):
                $error = '"emails not array"';
                break;
            case (empty($user->emails) ):
                $error = '"emails not suitable array"';
                break;
            case (!isset($user->real_name)):
                $error = '"no name"';
                break;
            case (!isset($user->id)):
                $error = '"no id"';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Yandex returned an error ' . $error . '.');
        }
        return $user;
    }

}
