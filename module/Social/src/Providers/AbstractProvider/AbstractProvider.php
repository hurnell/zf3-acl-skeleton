<?php

/**
 * Class AbstractProvider 
 *
 * @package     Social\Providers\AbstractProvider
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Providers\AbstractProvider;

use Social\Providers\ProviderInterface\ProviderInterface;
use Social\Service\SocialManager;
use Zend\Validator\Csrf;
use Zend\Http\Client;

/**
 * Class AbstractProvider handle generic functionality of all 
 * (extending) social media providers
 *
 * @package     Social\Providers\AbstractProvider
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 * 
 */
abstract class AbstractProvider implements ProviderInterface
{

    /**
     * The manager that handles basic logic for the Social module
     * 
     * @var SocialManager 
     */
    protected $socialManager;

    /**
     * The hashed string to add extra level of security 
     * Stands for cross site request forgery 
     * @var string hash 
     */
    protected $csrf;

    /**
     * The name of the social provider
     * Set in actual named provider
     * 
     * @var string
     */
    protected $providerName;

    /**
     * The basic parameters to append to the initial call to the provider
     * 
     * @var array 
     */
    protected $authorisationParams = [
        'client_id' => '',
        'redirect_uri' => '',
        'response_type' => 'code',
        'scope' => '',
        'state' => '',
    ];

    /**
     * Basic former for access params array
     * @var array 
     */
    protected $accessParams = [
        'client_id' => '',
        'client_secret' => '',
        'code' => '',
        'redirect_uri' => '',
    ];

    /**
     * The URI that the provider returns to
     * 
     * @var string
     */
    protected $callback;

    /**
     * social login or registration
     * 
     * @var string 
     */
    protected $action;

    /**
     * Constructor Instantiate (extending) class and pass Social manager and
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
     * Set provider name
     * Overridden by 'real' providers that give actual name of the provider
     */
    abstract protected function setProviderName();

    /**
     * Update authorisation parameters
     * Different (but simular) in each provider
     */
    abstract protected function updateAuthorisationParams();

    /**
     * Update Access Parameters
     * Different (but simular) in each provider
     * 
     * @param array $queryParams 
     */
    abstract protected function updateAccessParams($queryParams);

    /**
     * Handle Access Token Response
     * Different (but simular) in each provider
     * 
     * @param Client $client
     * @param Response $response
     */
    abstract protected function handleAccessTokenResponse(Client $client, $response);

    /**
     * Process user profile
     * Different in each provider
     * 
     * @param Response $response
     */
    abstract protected function processUserProfile($response);

    /**
     * Get the full redirect URL (including query string)
     * 
     * @param string $callback
     * @return string full redirect URL
     */
    public function getRedirectRoute($callback)
    {
        $this->callback = $callback;
        $query = $this->getQuery();
        return $this->baseAuthorisationUrl . '?' . $query;
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
        $this->checkReturnedQuery($queryParams);
        $this->accessParams['code'] = $queryParams['code'];
        $this->accessParams['client_id'] = $this->socialManager->getModuleOptions()->getClientId($this->providerName);
        $this->accessParams['client_secret'] = $this->socialManager->getModuleOptions()->getSecret($this->providerName);
        $this->accessParams['redirect_uri'] = $callback;
        $additionalHeaders = $this->updateAccessParams($queryParams);
        $client = $this->socialManager->getClient();
        $client->setUri($this->requestAccessTokenUrl);
        $headers = ['Content-Type' => 'application/x-www-form-urlencoded',];
        $client->setHeaders(array_merge($headers, $additionalHeaders));
        $client->setMethod('POST');
        $client->setParameterPost($this->accessParams);
        $response = $client->send();
        if (200 == $response->getStatusCode()) {
            return $this->handleAccessTokenResponse($client, $response);
        }
        throw new \Exception('AbstractProvider::sendClientRequest failed to return valid response.');
    }

    /**
     * Check the query string provided by the social provider
     * to ensure that it has the keys code and state and that the value of state 
     * corresponds to the CSRF value that was sent to the provider
     * 
     * @param array $params the params that need checking
     * @throws \Exception
     */
    protected function checkReturnedQuery($params)
    {
        if (!array_key_exists('code', $params) || !array_key_exists('state', $params) || !$this->checkCsrf($params['state'])) {
            throw new \Exception('The social provider returned invalid parameters.');
        }
    }

    /**
     * Get HTTP query string 
     * after setting up basic values for authorisationParams array
     * 
     * @return string the HTT{ query string
     */
    protected function getQuery()
    {
        $this->authorisationParams['state'] = $this->getCsrf();
        $this->authorisationParams['redirect_uri'] = $this->callback;
        $this->authorisationParams['client_id'] = $this->socialManager->getModuleOptions()->getClientId($this->providerName);
        /* update the parameters (each provider has own method) */
        $this->updateAuthorisationParams();
        return http_build_query($this->authorisationParams);
    }

    /**
     * Set (new) csrf hashed string
     * 
     * @return string csrf hash
     */
    protected function setCsrf()
    {
        $this->csrf = new Csrf();
        return $this->csrf;
    }

    /**
     * Get hashed security string 
     * 
     * @param boolean $regenerate whether to create a new one if one already exists
     * @return string hashed csrf
     */
    protected function getCsrf($regenerate = false)
    {
        return $this->setCsrf()->getHash($regenerate);
    }

    /**
     * Check whether the hash value matches the original created in getCsrf()
     * 
     * @param string $value the hashed value to check
     * @return boolean whether passed value is valid
     */
    protected function checkCsrf($value)
    {
        return $this->setCsrf()->isValid($value);
    }

}
