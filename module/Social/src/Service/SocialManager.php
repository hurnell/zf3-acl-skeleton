<?php

/**
 * Class SocialManager
 *
 * @package     Social\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Service;

use Social\Options\ModuleOptions;
use Zend\Http\Client;
use Social\Service\SocialAuthManager;
use Zend\Authentication\Result;
use Zend\Http\Client\Adapter\Curl;

/**
 * Class SocialManager
 * Handle logic needed for social platform log-in and registration
 *
 * @package     Social\Service
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialManager
{

    const SOCIAL_LOGIN = 'login';
    const SOCIAL_REGISTRATION = 'registration';
    const SOCIAL_LOGIN_OR_REGISTRATION = 'loginregistration';

    /**
     * Extended Zend\Http\Client
     * Used to make HTTP requests in providers
     * 
     * @var Client 
     */
    protected $client;

    /**
     * Class object used to aggregate social sign-in configuration options
     * 
     * @var ModuleOptions 
     */
    protected $moduleOptions;

    /**
     * Object that manages user authentication and authorisation
     * 
     * @var SocialAuthManager 
     */
    protected $socialAuthManager;

    /**
     * Session container used to persist action between social auth request and response
     * 
     * @var Zend\Session\Container 
     */
    protected $sessionContainer;

    /**
     * Constructor
     * 
     * @param ModuleOptions $options
     * @param SocialAuthManager $socialAuthManager
     * @param SessionContainer $sessionContainer
     */
    public function __construct(ModuleOptions $options, SocialAuthManager $socialAuthManager, $sessionContainer)
    {
        $this->moduleOptions = $options;
        $this->socialAuthManager = $socialAuthManager;
        $this->sessionContainer = $sessionContainer;
    }

    /**
     * Put current action in session container
     * 
     * @param string $action
     */
    public function setAction($action)
    {
        $this->sessionContainer->action = $action;
    }

    /**
     * Get action name from session container
     * 
     * @return string
     */
    public function getAction()
    {
        return $this->sessionContainer->action;
    }

    /**
     * Put current locale in session container
     * 
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->sessionContainer->locale = $locale;
    }

    /**
     * Get locale name from session container
     * 
     * @return string
     */
    public function getLocale()
    {
        return $this->sessionContainer->locale;
    }

    /**
     * Get Client used to make HTTP requests
     * 
     * @return Zend\Http\Client
     */
    public function getClient()
    {
        if (null === $this->client) {
            $this->client = new Client();
            $options = [
                'adapter' => Curl::class,
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            ];
            $this->client->setOptions($options);
        }
        return $this->client;
    }

    /**
     * Get module options 
     * 
     * @return ModulOptions class object
     */
    public function getModuleOptions()
    {
        return $this->moduleOptions;
    }

    /**
     * Create class object for social media platform
     * 
     * @param string $providerName
     * @return boolean|Socail Provider class
     */
    public function startProvider($providerName)
    {
        $enabledProviders = $this->getModuleOptions()->getEnabledProviders();
        if (in_array($providerName, $enabledProviders)) {
            $class = 'Social\Providers\\' . str_replace(' ', '', ucwords(str_replace('_', ' ', $providerName))) . 'Provider';
            if (class_exists($class)) {
                $provider = new $class($this);
                return $provider;
            }
        }
        return false;
    }

    /**
     * Execute logic for adding status messages and status when someone tries 
     * to log in with social media platform
     * 
     * @param array $clientRequestResult
     * @return Result
     */
    public function handleSocialAuthRedirect($clientRequestResult)
    {
        $result = new Result(Result::FAILURE_UNCATEGORIZED, null, ['error' => 'An unknown error occured.']);
        if (is_array($clientRequestResult) && array_key_exists('email', $clientRequestResult) && array_key_exists('name', $clientRequestResult)) {
            $action = $this->getAction();
            $clientRequestResult['action'] = $action;
            switch ($action) {
                case self::SOCIAL_LOGIN :
                    $result = $this->socialAuthManager->completeSocialLogin($clientRequestResult);
                    break;
                case self::SOCIAL_REGISTRATION:
                    $result = $this->socialAuthManager->completeSocialRegistration($clientRequestResult);
                    break;
                case self::SOCIAL_LOGIN_OR_REGISTRATION:
                    $result = $this->socialAuthManager->completeSocialLoginOrRegistration($clientRequestResult);
                    break;
            }
        }
        return $result;
    }

}
