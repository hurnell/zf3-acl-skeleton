<?php

/**
 * Class FlagNavigation
 *
 * @package     Translate\View\Helper
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Translate\Service\LanguageManager;
use Zend\Router\Http\RouteMatch;

/**
 * This view helper class displays language navigation on all pages
 *
 * @package     Translate\View\Helper
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class FlagNavigation extends AbstractHelper
{

    /**
     * Service that handles translation logic (model)
     * 
     * @var TranslationManager
     */
    protected $h;

    /**
     * The locations of the enabled language flag images
     * 
     * @var array
     */
    protected $flagDir;

    /**
     * Instantiate class and inject TranslationManager service
     * 
     * @param TranslationManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * Renders the flag based navigation.
     * 
     * @param view $view
     * @return string HTML code of the flag navigation.
     */
    public function render($view)
    {
        $links = '';
        $languages = $this->languageManager->getEnabledLanguages();
        foreach ($languages as $language) {
            $updatedUrl = $this->getUpdatedUrl($language['locale'], $view);
            if ($updatedUrl) {
                $title = ' title="' . $language['language'] . '" ';
                $links .= '<a href="' . $updatedUrl . '"><img  src="' . $language['src'] . '" ' . $title . ' /></a>';
            }
        }
        return $links;
    }

    /**
     * Get new URL with updated language locale 
     * 
     * @param string $locale
     * @param view $view
     * @return string the URL of the page rendered into the locale language requested
     */
    protected function getUpdatedUrl($locale, $view)
    {
        $updatedUrl = false;
        $routeMatch = $this->languageManager->getRouteMatch();
        if (isset($routeMatch)) {
            $params = $routeMatch->getParams();
            if ($locale !== $params['locale'] && array_key_exists('locale', $params)) {
                $params['locale'] = $locale;
                $updatedUrl = $view->url($routeMatch->getMatchedRouteName(), $params);
            }
        }
        return $updatedUrl;
    }

}
