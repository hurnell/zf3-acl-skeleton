<?php

/**
 * Class TranslateControllerPlugin
 *
 * @package     Translate\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

/**
 * Controller plugin TranslateControllerPlugin that is responsible for translating messages in a controller
 *
 * @package     Translate\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslateControllerPlugin extends AbstractPlugin
{

    /**
     * The translator
     * 
     * @var Zend\Mvc\I18n\Translator 
     */
    protected $translator;

    /**
     * Constructor
     * 
     * @param Zend\Mvc\I18n\Translator $translator
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * 
     * Translate a message
     * 
     * @param string $message the untranslated message
     * @param string $textDomain text domain which is an alias for controller alias NOTE THAT THIS IS NOT USED FOR PRESENT APPLICATION
     * @param string $locale the locale for instance en_GB NOTE THAT THIS IS NOT USED FOR PRESENT APPLICATION
     * @return string the translated message
     */
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        return $this->translator->translate($message, $textDomain, $locale);
    }

}
