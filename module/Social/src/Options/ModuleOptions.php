<?php

/**
 * Class ModuleOptions 
 *
 * @package     Social\Options
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Stdlib\Exception\BadMethodCallException;

/**
 * Class ModuleOptions where configuration settings are initialised and can be 
 * introspected by the rest of the application
 *
 * @package     Social\Options
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ModuleOptions extends AbstractOptions {

    /**
     * Doctrine User Entity
     * 
     * @var User  
     */
    protected $userEntity;

    /**
     * 
     * @var authentication service to user  
     */
    protected $authenticationService;

    /**
     * The social auth header text on registration page 
     * 
     * @var string 
     */
    protected $registrationHeader;

    /**
     * The social auth header text on login page 
     * NOTE that if login and or registration is enabled this header should reflect that
     * 
     * @var string 
     */
    protected $loginHeader;

    /**
     * The social auth paragraph text on login page
     * NOTE that if login and or registration is enabled this header should reflect that
     * 
     * @var string 
     */
    protected $loginText;

    /**
     * The social auth paragraph text on registration page 
     * 
     * @var string 
     */
    protected $registrationText;

    /**
     * Turn on strict options mode
     */
    protected $__strictMode__ = true;

    /**
     * List of social auth providers that are available to the application
     * not necessarily enabled
     * 
     * @var array 
     */
    protected $availableProviders = [
        'facebook',
        'foursquare',
        'git_hub',
        'google',
        'linked_in',
        'live',
        'twitter',
        'yahoo',
        'yandex',
    ];

    /* OTHER PROVIDERS NOT IMPLEMENTED */
    /*
      'aol', //no reply from The AOL Reader Team
      'bitbucket',
      'tumblr',
      'mailru',
      'weibo', //cannot register - not possible to use NL phone number
      'odnoklassniki',
      'vkontakte',
      'instagram', // does not return user e-mail address??
     */

    /**
     * List of providers to be populated based on social-config.global.php 
     * in autoload folder
     * 
     * @var array 
     */
    protected $enabledProviders = [];

    /**
     * Associative array of client ids (values) with provider name as key
     * NOTE some providers use different term for client id
     * 
     * @var array 
     */
    protected $clientIds = [];

    /**
     * Associative array of client secrets (values) with provider name as keys
     * NOTE some providers use different term for client secret
     * 
     * @var array 
     */
    protected $secrets = [];

    /**
     * Constructor
     * 
     * @param array $options
     */
    public function __construct(array $options) {
        parent::__construct($options);
    }

    /**
     * Magic method that overrides AbstractOptions method call functions based on
     * configuration keys in social-config files in autoload folder
     * 
     * @param string $key 
     * @param string $value
     * @return null so that exception is not thrown in strict mode
     * @throws BadMethodCallException
     */
    public function __set($key, $value) {
        $setter = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
        if (is_callable([$this, $setter])) {
            $this->{$setter}($value);
            return;
        }

        if ($this->__strictMode__) {
            throw new BadMethodCallException(sprintf(
                            'The option "%s" does not have a callable "%s" ("%s") setter method which must be defined', $key, 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key))), $setter
            ));
        }
    }

    /**
     * get an array of enabled providers
     *
     * @return array
     */
    public function getEnabledProviders() {
        $providers = [];
        foreach ($this->enabledProviders as $key => $value) {
            if (true == $value) {
                $providers[] = $key;
            }
        }
        return $providers;
    }

    /**
     * Get the client id for requested provider
     * 
     * @param string $provider
     * @return false|string the client id
     */
    public function getClientId($provider) {
        $result = false;
        if (array_key_exists($provider, $this->clientIds)) {
            $result = $this->clientIds[$provider];
        }
        return $result;
    }

    /**
     * Alias for getClientId
     * 
     * @param string $provider name of social provider
     * @return false|string the consumer key
     */
    public function getConsumerKey($provider) {
        return $this->getClientId($provider);
    }

    /**
     * Get the client secret for requested provider
     * 
     * @param string $provider name of social provider
     * @return string
     */
    public function getSecret($provider) {
        $result = false;
        if (array_key_exists($provider, $this->secrets)) {
            $result = $this->secrets[$provider];
        }
        return $result;
    }

    /* FACEBOOK */

    /**
     * Set whether Facebook is enabled
     * 
     * @param boolean $enabled
     */
    public function setFacebookEnabled($enabled) {
        if (in_array('facebook', $this->availableProviders)) {
            $this->enabledProviders['facebook'] = $enabled;
        }
    }

    /**
     * Set Facebook client ID
     * 
     * @param string $clientId facebook client id
     */
    public function setFacebookClientId($clientId) {
        $this->clientIds['facebook'] = $clientId;
    }

    /**
     * Set Facebook secret
     * 
     * @param string $secret Facebook secret
     */
    public function setFacebookSecret($secret) {
        $this->secrets['facebook'] = $secret;
    }

    /* FOURSQUARE */

    /**
     * Set whether FOURSQUARE is enabled
     * 
     * @param boolean $enabled
     */
    public function setFoursquareEnabled($enabled) {
        if (in_array('foursquare', $this->availableProviders)) {
            $this->enabledProviders['foursquare'] = $enabled;
        }
    }

    /**
     * Set FOURSQUARE client ID
     * 
     * @param string $clientId FOURSQUARE client id
     */
    public function setFoursquareClientId($clientId) {
        $this->clientIds['foursquare'] = $clientId;
    }

    /**
     * Set FOURSQUARE secret
     * 
     * @param string $secret FOURSQUARE secret
     */
    public function setFoursquareSecret($secret) {
        $this->secrets['foursquare'] = $secret;
    }

    /* GOOGLE */

    /**
     * Set whether GOOGLE is enabled
     * 
     * @param boolean $enabled
     */
    public function setGoogleEnabled($enabled) {
        if (in_array('google', $this->availableProviders)) {
            $this->enabledProviders['google'] = $enabled;
        }
    }

    /**
     * Set GOOGLE client ID
     * 
     * @param string $clientId GOOGLE client id
     */
    public function setGoogleClientId($clientId) {
        $this->clientIds['google'] = $clientId;
    }

    /**
     * Set GOOGLE secret
     * 
     * @param string $secret GOOGLE secret
     */
    public function setGoogleSecret($secret) {
        $this->secrets['google'] = $secret;
    }

    /* GITHUB */

    /**
     * Set whether GITHUB is enabled
     * 
     * @param boolean $enabled
     */
    public function setGitHubEnabled($enabled) {
        if (in_array('git_hub', $this->availableProviders)) {
            $this->enabledProviders['git_hub'] = $enabled;
        }
    }

    /**
     * Set GITHUB client ID
     * 
     * @param string $clientId GITHUB client id
     */
    public function setGitHubClientId($clientId) {
        $this->clientIds['git_hub'] = $clientId;
    }

    /**
     * Set GITHUB secret
     * 
     * @param string $secret GITHUB secret
     */
    public function setGitHubSecret($secret) {
        $this->secrets['git_hub'] = $secret;
    }

    /* LINEKDIN */

    /**
     * Set whether LINEKDIN is enabled
     * 
     * @param boolean $enabled
     */
    public function setLinkedInEnabled($enabled) {
        if (in_array('linked_in', $this->availableProviders)) {
            $this->enabledProviders['linked_in'] = $enabled;
        }
    }

    /**
     * Set LINEKDIN client ID
     * 
     * @param string $clientId LINEKDIN client id
     */
    public function setLinkedInClientId($clientId) {
        $this->clientIds['linked_in'] = $clientId;
    }

    /**
     * Set LINEKDIN secret
     * 
     * @param string $secret LINEKDIN secret
     */
    public function setLinkedInSecret($secret) {
        $this->secrets['linked_in'] = $secret;
    }

    /* LIVE */

    /**
     * Set whether LIVE is enabled
     * 
     * @param boolean $enabled
     */
    public function setLiveEnabled($enabled) {
        if (in_array('live', $this->availableProviders)) {
            $this->enabledProviders['live'] = $enabled;
        }
    }

    /**
     * Set LIVE client ID
     * 
     * @param string $clientId LIVE client id
     */
    public function setLiveClientId($clientId) {
        $this->clientIds['live'] = $clientId;
    }

    /**
     * Set LIVE secret
     * 
     * @param string $secret LIVE secret
     */
    public function setLiveSecret($secret) {
        $this->secrets['live'] = $secret;
    }

    /* TWITTER */

    /**
     * Set whether TWITTER is enabled
     * 
     * @param boolean $enabled
     */
    public function setTwitterEnabled($enabled) {
        if (in_array('twitter', $this->availableProviders)) {
            $this->enabledProviders['twitter'] = $enabled;
        }
    }

    /**
     * Set TWITTER client ID
     * 
     * @param string $clientId TWITTER client id
     */
    public function setTwitterConsumerKey($clientId) {
        $this->clientIds['twitter'] = $clientId;
    }

    /**
     * Set TWITTER secret
     * 
     * @param string $secret TWITTER secret
     */
    public function setTwitterConsumerSecret($secret) {
        $this->secrets['twitter'] = $secret;
    }

    /* YAHOO */

    /**
     * Set whether YAHOO is enabled
     * 
     * @param boolean $enabled
     */
    public function setYahooEnabled($enabled) {
        if (in_array('yahoo', $this->availableProviders)) {
            $this->enabledProviders['yahoo'] = $enabled;
        }
    }

    /**
     * Set YAHOO client ID
     * 
     * @param string $clientId YAHOO client id
     */
    public function setYahooClientId($clientId) {
        $this->clientIds['yahoo'] = $clientId;
    }

    /**
     * Set YAHOO secret
     * 
     * @param string $secret YAHOO secret
     */
    public function setYahooSecret($secret) {
        $this->secrets['yahoo'] = $secret;
    }

    /* YANDEX */

    /**
     * Set whether YANDEX is enabled
     * 
     * @param boolean $enabled
     */
    public function setYandexEnabled($enabled) {
        if (in_array('yandex', $this->availableProviders)) {
            $this->enabledProviders['yandex'] = $enabled;
        }
    }

    /**
     * Set YANDEX client ID
     * 
     * @param string $clientId YANDEX client id
     */
    public function setYandexClientId($clientId) {
        $this->clientIds['yandex'] = $clientId;
    }

    /**
     * Set YANDEX secret
     * 
     * @param string $secret YANDEX secret
     */
    public function setYandexSecret($secret) {
        $this->secrets['yandex'] = $secret;
    }

    /* GLOBAL */

    /**
     * Set the user entity object
     * 
     * @param User $userEntity
     */
    public function setDoctrineUserEntity($userEntity) {
        $this->userEntity = $userEntity;
    }

    /**
     * Get the user entity object
     * 
     * @return User user entity object
     */
    public function getDoctrineUserEntity() {
        return $this->userEntity;
    }

    /**
     * Set the Authentication Service
     * 
     * @param Zend\Authentication\AuthenticationService $authenticationService
     */
    public function setAuthenticationService($authenticationService) {
        $this->authenticationService = $authenticationService;
    }

    /**
     * Get the Authentication Service
     * 
     * @return Zend\Authentication\AuthenticationService
     */
    public function getAuthenticationService() {
        return $this->authenticationService;
    }

    /**
     * Set login header
     * 
     * @param string $loginHeader
     */
    public function setLoginHeader($loginHeader) {
        $this->loginHeader = $loginHeader;
    }

    /**
     * Get login header
     * 
     * @return string
     */
    public function getLoginHeader() {
        return $this->loginHeader;
    }

    /**
     * Set login text
     * 
     * @param string $loginText
     */
    public function setLoginText($loginText) {
        $this->loginText = $loginText;
    }

    /**
     * Get login text
     * 
     * @return string
     */
    public function getLoginText() {
        return $this->loginText;
    }

    /**
     * Set registration page header
     * 
     * @param string $registrationHeader
     */
    public function setRegistrationHeader($registrationHeader) {
        $this->registrationHeader = $registrationHeader;
    }

    /**
     * Get registration page header
     * 
     * @return string
     */
    public function getRegistrationHeader() {
        return $this->registrationHeader;
    }

    /**
     * Set registration page text
     * 
     * @param string $registrationText
     */
    public function setRegistrationText($registrationText) {
        $this->registrationText = $registrationText;
    }

    /**
     * Get registration page text
     * 
     * @return string
     */
    public function getRegistrationText() {
        return $this->registrationText;
    }

}
