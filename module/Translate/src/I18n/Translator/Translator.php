<?php

/**
 * Class Translator
 *
 * @package     Translate\I18n\Translator
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\I18n\Translator;

use Zend\I18n\Translator\Translator as I18nTranslator;
use Translate\Service\TranslationSaverInterface;

/**
 * Class Translator which overrides Zend\I18n\Translator\Translator
 * So that it uses updated logic and attaches the translation saver
 *
 * @package     Translate\I18n\Translator
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Translator extends I18nTranslator
{

    /**
     * Service that saves translated messages to PO 7 MO files
     * @var TranslationSaverInterface 
     */
    protected $translationSaver;

    /**
     * Set the translation saver
     * 
     * @param TranslationSaverInterface $translationSaver the class that updated the translation files
     */
    public function setTranslationSaver(TranslationSaverInterface $translationSaver)
    {
        $this->translationSaver = $translationSaver;
    }

    /**
     * Get the translation saver 
     * 
     * @return TranslationSaverInterface 
     */
    public function getTranslationSaver()
    {
        return $this->translationSaver;
    }

    /**
     * Translate a message (string).
     *
     * @param  string $message the untranslated string
     * @param  string $textDomain the text domain passed to translate method (always default)
     * @param  string $passedLocale locale passed to translate method normally null since locale is defined in URI
     * @return string the translated message
     */
    public function translate($message, $textDomain = 'default', $passedLocale = null)
    {
        $locale = ($passedLocale ?: $this->getLocale());
        $translation = $this->getTranslatedMessage($message, $locale, $textDomain);
        $trimmed = trim($translation);
        if ($translation !== null && $trimmed !== '') {
            return $translation;
        } else if (null !== ($saver = $this->getTranslationSaver()) && $locale !== '$locale' && $translation == null) {
            $saver->saveMissingTranslation($message, $locale, $translation);
        }
        if (null !== ($fallbackLocale = $this->getFallbackLocale()) && $locale !== $fallbackLocale
        ) {
            return $this->translate($message, $textDomain, $fallbackLocale);
        }
        return $message;
    }

}
