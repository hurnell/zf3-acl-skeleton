<?php

/**
 * Class TwitterProvider 
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Providers;

use Social\Providers\ProviderInterface\ProviderInterface;
use Social\Service\SocialManager;

/**
 * Class GoogleProvider 
 * Social Media OAuth1 provider for TWITTER
 *
 * @package     Social\Providers
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TwitterProvider implements ProviderInterface
{

    /**
     * TWITTER's base authorisation URL
     * 
     * @var string 
     */
    protected $baseUrl = 'https://api.twitter.com/'; // oauth/authenticate

    /**
     * Part of URL to append
     * 
     * @var string 
     */
    protected $preUrl = 'oauth/request_token';

    /**
     * Type of hash to use
     * 
     * @var string 
     */
    protected $signatureMethod = 'HMAC-SHA1';

    /**
     * TWITTER API version
     * 
     * @var string
     */
    protected $oauthVersion = '1.0';

    /**
     * Default parameters to use in the URL request
     * 
     * @var array 
     */
    protected $defaultParams = [];

    /**
     * social login or registration
     * 
     * @var string 
     */
    protected $action;

    /**
     * Constructor Instantiate class and pass Social manager and
     * set the extending social provider name.
     * 
     * @param SocialManager $socialManager
     */
    public function __construct(SocialManager $socialManager)
    {
        $this->socialManager = $socialManager;
        $this->setProviderName();
    }

    /**
     * Set the provider name
     */
    protected function setProviderName()
    {
        $this->providerName = 'twitter';
    }

    /**
     * Get the full redirect URL (including query string)
     * 
     * @param string $callback
     * @return string full redirect URL
     */
    public function getRedirectRoute($callback)
    {
        $this->callback = $callback;
        $this->setDefaultParams();
        $path = 'oauth/request_token';
        if (false !== $response = $this->makeRequest($path, 'POST')) {
            $result = [];
            parse_str($response->getBody(), $result);
            if (200 == $response->getStatusCode() && array_key_exists('oauth_callback_confirmed', $result) && $result['oauth_callback_confirmed'] == 'true' && array_key_exists('oauth_token', $result) && array_key_exists('oauth_token_secret', $result)) {
                return $this->baseUrl . 'oauth/authenticate' . '?' . 'oauth_token=' . $result['oauth_token'];
            }
        }
        throw new \Exception('Twitter returned an error');
    }

    /**
     * Send Client Request
     * Form client request URL with query params and send via Zend Client
     * 
     * @param string $callback the callback URL
     * @param array $queryParams parameters to append to end of callback URL
     * @return array that contains the user profile
     */
    public function sendClientRequest($callback, $queryParams)
    {
        $this->callback = $callback;
        if (array_key_exists('oauth_token', $queryParams) && array_key_exists('oauth_verifier', $queryParams)) {
            $this->setDefaultParams($queryParams['oauth_token'], $queryParams['oauth_verifier']);
            $path = 'oauth/access_token';
            if (false !== $response = $this->makeRequest($path, 'POST', ['oauth_verifier' => $queryParams['oauth_verifier']])) {
                $result = [];
                parse_str($response->getBody(), $result);
                if (200 == $response->getStatusCode()) {
                    $result = [];
                    parse_str($response->getBody(), $result);
                    return $this->getCredentials($result);
                }
            }
        }
        throw new \Exception('Twitter returned an error (1).');
    }

    /**
     * Get TWITTER user's credentials
     * 
     * @param type $queryParams
     * @return type
     * @throws \Exception
     */
    protected function getCredentials($queryParams)
    {
        $this->checkCredentials($queryParams);
        $this->setDefaultParams($queryParams['oauth_token']);
        $path = '1.1/account/verify_credentials.json';
        $params = array('include_email' => 'true', 'include_entities' => 'false', 'skip_status' => 'true');
        if (false !== $response = $this->makeRequest($path, 'GET', $params, $queryParams['oauth_token_secret'])) {
            $result = [];
            parse_str($response->getBody(), $result);
            if (200 == $response->getStatusCode()) {
                $user = json_decode($response->getBody());
                if (isset($user->email)) {
                    return [
                        'name' => var_export($user->name, true),
                        'email' => $user->email,
                        'id' => $user->id,
                        'provider' => $this->providerName
                    ];
                }
            }
        }
        throw new \Exception('Twitter could not get valid credentials.');
    }

    /**
     * CHeck that the returned array has the required keys
     * 
     * @param array $queryParams
     * @return JSON object
     * @throws \Exception when returned object does not conform to requirements
     */
    protected function checkCredentials($queryParams)
    {
        $error = '';
        switch (true) {
            case (!array_key_exists('oauth_token', $queryParams)):
                $error = 'oauth_token not in array';
                break;
            case (!array_key_exists('oauth_token_secret', $queryParams)):
                $error = 'oauth_token_secret not in array';
                break;
        }
        if ('' !== $error) {
            throw new \Exception('Twitter returned an error "' . $error . '".');
        }
    }

    /**
     * Make HTTP request
     * 
     * @param string $path
     * @param string $method
     * @param array $params
     * @param string $tokenSecret
     * @return Response
     */
    protected function makeRequest($path, $method, $params = [], $tokenSecret = '')
    {
        $authorization = $this->buildAuthorisation($path, $method, $tokenSecret, $params);
        $client = $this->socialManager->getClient();
        $client->resetParameters();
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => $authorization,
            'User-Agent' => 'TwitterOAuth (+https://twitteroauth.com) Adapted for Zend http client',
            'Expect' => '',
        ];
        switch (true) {
            case 'POST' == $method && count($params) > 0:
                $client->setParameterPost($params);
                break;
            case 'GET' == $method && count($params) > 0:
                $path .= '?' . http_build_query($params);
                break;
            default:
                break;
        }
        $client->setUri($this->baseUrl . $path);
        $client->setMethod($method);
        $client->setHeaders($headers);
        return $client->send();
    }

    /**
     * Populate defaultParams with TWITTER specific values
     * 
     * @param string|false $oauthToken
     * @param string|false $oauthVerifier
     */
    protected function setDefaultParams($oauthToken = false, $oauthVerifier = false)
    {
        $this->defaultParams = [
            'oauth_consumer_key' => $this->socialManager->getModuleOptions()->getConsumerKey('twitter'),
            'oauth_nonce' => $this->getNonce(),
            'oauth_signature_method' => $this->signatureMethod,
            'oauth_timestamp' => $this->getTimestamp(),
            'oauth_version' => $this->oauthVersion,
            'oauth_extra' => 'action'
        ];
        if (false !== $oauthToken) {
            $this->defaultParams['oauth_token'] = $oauthToken;
        }
        if (false !== $oauthVerifier) {
            $this->defaultParams['oauth_verifier'] = $oauthVerifier;
        }
    }

    /**
     * Build special authorisation hashed string specific to twitter
     * 
     * @param string $path
     * @param string $method
     * @param string $tokenSecret
     * @param array $params
     * @return string
     * @throws TwitterOAuthException
     */
    protected function buildAuthorisation($path, $method, $tokenSecret = '', $params = [])
    {
        $first = true;
        $this->defaultParams['oauth_signature'] = $this->getSignature(array_merge($this->defaultParams, $params), $path, $method, $tokenSecret);
        $out = 'OAuth';
        foreach ($this->defaultParams as $k => $v) {
            if (substr($k, 0, 5) != "oauth") {
                continue;
            }
            if (is_array($v)) {
                throw new \Exception('Arrays not supported in headers');
            }
            $out .= ($first) ? ' ' : ', ';
            $out .= $this->urlencodeRfc3986($k) . '="' . $this->urlencodeRfc3986($v) . '"';
            $first = false;
        }
        return $out;
    }

    /**
     * Get specially constructed hashed string for TWITTER
     * 
     * @param array $params
     * @param string $path
     * @param string $method
     * @param string $tokenSecret
     * @return string
     */
    protected function getSignature($params, $path, $method, $tokenSecret = '')
    {

        $signatureBase = $this->getSignatureBaseString($params, $path, $method);

        $secret = $this->socialManager->getModuleOptions()->getSecret('twitter');
        $key_parts = [$secret, $tokenSecret];
        $key = implode('&', $this->urlencodeRfc3986($key_parts));

        return base64_encode(hash_hmac('sha1', $signatureBase, $key, true));
    }

    /**
     * Get special query string ordered appropriately
     * 
     * @param array $params
     * @param string $path
     * @param string $method
     * @return string
     */
    protected function getSignatureBaseString($params, $path, $method)
    {
        ksort($params);
        $parts = [
            $method,
            $this->baseUrl . $path,
            http_build_query($params)
        ];
        return implode('&', $this->urlencodeRfc3986($parts));
    }

    /**
     * Get hashed string
     * 
     * @return string
     */
    protected function getNonce()
    {
        return md5(microtime() . mt_rand());
    }

    /**
     * Get time stamp
     * 
     * @return string
     */
    protected function getTimestamp()
    {
        return time();
    }

    /**
     * Special URL encoding
     * 
     * @param array|string $input
     * @return string
     */
    protected function urlencodeRfc3986($input)
    {
        $output = '';
        if (is_array($input)) {
            $output = array_map([$this, 'urlencodeRfc3986'], $input);
        } elseif (is_scalar($input)) {
            $output = rawurlencode($input);
        }
        return $output;
    }

}
