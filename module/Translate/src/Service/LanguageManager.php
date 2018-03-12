<?php

/**
 * Class LanguageManager 
 *
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace Translate\Service;

use Zend\Mvc\MvcEvent;
use Zend\Router\Http\RouteMatch;

/**
 * Service that handles application languages and locales
 * 
 * @package     Translate\Service
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class LanguageManager
{

    /**
     * Matched Route
     * 
     * @var RouteMatch 
     */
    protected $routeMatch;

    /**
     * Array of all locales (as passed in configuration)
     * 
     * @var array 
     */
    protected $locales = [];

    /**
     * Array of languages (keys are locales values are the languages)
     * 
     * @var array 
     */
    protected $languages = [];

    /**
     * Array of locales (defined in config file)
     * 
     * @var array 
     */
    protected $enabledLocales = [];

    /**
     * Associative array of languages with keys for enabled available & missing 
     * @var array 
     */
    protected $langauageArray;

    /**
     * Local (to server) path to flags folder
     * 
     * @var atring 
     */
    protected $localPath = './public/img/flags/';

    /**
     * public path to flags folder
     * 
     * @var type 
     */
    protected $publicPath = '/img/flags/';

    /**
     * Instantiate LanguageManager object and inject $entityManager
     * 
     * @param array $languages
     */
    public function __construct($languages)
    {
        $this->localPath = str_replace('/', DIRECTORY_SEPARATOR, $this->localPath);
        $this->languages = $languages;
        $this->getLanguagesArray();
    }

    /**
     * on dispatch listener hook that sets the route that is matched
     * 
     * @param MvcEvent $event
     */
    public function onDispatch(MvcEvent $event)
    {
        $this->routeMatch = $event->getRouteMatch();
    }

    /**
     * get array of all parameters that were passed to the application
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->routeMatch->getParams();
    }

    /**
     * Get the present locale
     * 
     * @return string 
     */
    public function getLocale()
    {
        return $this->routeMatch->getParam('locale', 'en_GB');
    }

    /**
     * Get the matched route that was passed when listener was called
     * 
     * @return RouteMatch
     */
    public function getRouteMatch()
    {
        return $this->routeMatch;
    }

    /**
     * Get the present controller (alias for text_domain)
     * @return string
     */
    public function getTextDomain()
    {
        return $this->routeMatch->getParam('controller');
    }

    /**
     * Get array of languages with keys corresponding to:
     * enabled (languages that are available to the end user)
     * available (languages that have a flag and can be made available to the end user)
     * missing (languages that are defined in the config files but there is no flag available)
     * 
     * @return array
     */
    public function getLanguagesArray()
    {
        if (!isset($this->langauageArray)) {
            $this->langauageArray = ['enabled' => [], 'available' => [], 'missing' => []];
            foreach ($this->languages as $locale => $language) {
                $this->buildLanguageArray($locale, $language);
            }
        }
        return $this->langauageArray;
    }

    /**
     * Function that builds the array for getLanguagesArray placing each language 
     * in its appropriate category
     * 
     * @param string $locale
     * @param string $language
     */
    protected function buildLanguageArray($locale, $language)
    {
        $isAavailable = file_exists($this->localPath . $locale . '.png');
        $isEnabled = file_exists($this->localPath . 'enabled' . DIRECTORY_SEPARATOR . $locale . '.png');
        $temp = ['locale' => $locale, 'language' => $language];
        if ($isEnabled) {
            $this->enabledLocales[] = $locale;
            $temp['src'] = $this->publicPath . 'enabled/' . $locale . '.png';
            $this->langauageArray['enabled'][] = $temp;
        } else if ($isAavailable) {
            $temp['src'] = $this->publicPath . $locale . '.png';
            $this->langauageArray ['available'][] = $temp;
        } else {
            $this->langauageArray ['missing'][$locale] = $language;
        }
    }

    /**
     * Get array of language locales that are enabled for the end user
     * 
     * @return array
     */
    public function getEnabledLanguages()
    {
        return $this->langauageArray['enabled'];
    }

    /**
     * Get array of all locales that are defined in configuration files
     * 
     * @return array
     */
    public function getAllLocales()
    {
        $this->getLanguagesArray();
        return array_keys($this->languages);
    }

    /**
     * Get array of locales that are enabled for the end user
     * 
     * @return array
     */
    public function getEnabledLocales()
    {
        return $this->enabledLocales;
    }

    /**
     * Enable or disable given language
     * 
     * @param string $locale the locale to be enabled or disabled
     * @param string $changeType enable|disable language
     * @return boolean
     * @throws \Exception when the change key does not meed the criteria
     */
    public function toggleLanguage($locale, $changeType)
    {
        if ('enable' !== $changeType && 'disable' !== $changeType) {
            throw new \Exception('Change Type must be "enable" or "disable" it was: ' . $changeType);
        }
        $result = false;
        $enabledPath = $this->localPath . 'enabled' . DIRECTORY_SEPARATOR . $locale . '.png';
        $availablePath = $this->localPath . $locale . '.png';
        if ('disable' == $changeType) {
            $result = file_exists($enabledPath) ? unlink($enabledPath) : false;
        } else if ('enable' == $changeType) {
            $result = file_exists($availablePath) ? copy($availablePath, $enabledPath) : false;
        }
        return $result;
    }

}
