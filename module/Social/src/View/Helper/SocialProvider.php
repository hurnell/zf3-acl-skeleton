<?php

/**
 * Class SocialProvider
 *
 * @package     Social\View\Helper
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Social\Options\ModuleOptions;

/**
 * Class SocialProvider View helper used to render links for social media 
 * platform login and registration
 *
 * @package     Social\View\Helper
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialProvider extends AbstractHelper
{

    /**
     * Class object that aggregates social configuration options
     * 
     * @var ModuleOptions 
     */
    protected $moduleOptions;

    /**
     * URL string in which the provider can be substituted for the now known provider
     * 
     * @var string
     */
    protected $substitutableUrl;

    /**
     * Constructor
     * 
     * @param ModuleOptions $moduleOptions
     */
    public function __construct(ModuleOptions $moduleOptions)
    {
        $this->moduleOptions = $moduleOptions;
    }

    /**
     * 
     * Render the provider list of images for login page
     * 
     * @return string
     * @param string $type
     * @return string
     */
    public function render($type)
    {
        return 'registration' == $type ? $this->renderSocialRegistration() : $this->renderLogin();
    }

    /**
     * Render the provider list of images for registration page
     * 
     * @return string
     */
    public function renderSocialRegistration()
    {
        $html = '<div id="social-sign-in-div">';
        $html .= '<h1>' . $this->translate($this->moduleOptions->getRegistrationHeader()) . '</h1>';
        $html .= '<p>' . $this->translate($this->moduleOptions->getRegistrationText()) . '</p>';
        $html .= $this->getProviderList();
        $html .= '</div>';
        return $html;
    }

    /**
     * Render the provider list of images for login and/or registration page
     * 
     * @return string
     */
    public function renderLogin()
    {
        $html = '<div id="social-sign-in-div">';
        $html .= '<h1>' . $this->translate($this->moduleOptions->getLoginHeader()) . '</h1>';
        $html .= '<p>' . $this->translate($this->moduleOptions->getLoginText()) . '</p>';
        $html .= $this->getProviderList();
        $html .= '</div>';
        return $html;
    }

    /**
     * Translate message
     * 
     * @param string $message
     * @return string
     */
    protected function translate($message)
    {
        return $this->getView()->translate($message);
    }

    /**
     * Set the base URL that can later have the provider name substituted
     * 
     * @param string $url
     * @return $this
     */
    public function setBaseUrl($url)
    {
        $this->substitutableUrl = $url;
        return $this;
    }

    /**
     * Get unordered list of HTTP anchor elements with image files
     * 
     * @return string
     */
    protected function getProviderList()
    {
        $providers = $this->moduleOptions->getEnabledProviders();
        $out = '';
        if (0 < count($providers)) {
            $out .= '<ul class="social-providers-list">';
            foreach ($providers as $provider) {
                $href = str_replace('substitutable-provider', $provider, $this->substitutableUrl);
                $out .= '<li>';
                $out .= '<a href="' . $href . '" title="">';
                $out .= '<img src="/img/providers/' . $provider . '.png" />';
                $out .= '</a>';
                $out .= '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }

}
