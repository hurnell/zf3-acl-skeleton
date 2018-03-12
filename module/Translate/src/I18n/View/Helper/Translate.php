<?php

/**
 * Class Translate 
 *
 * @package     Translate\I18n\View\Helper
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\I18n\View\Helper;

use Zend\I18n\View\Helper\AbstractTranslatorHelper;
use Zend\I18n\Exception\RuntimeException;

/**
 * View helper for translating messages.
 *
 * @package     Translate\I18n\View\Helper
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Translate extends AbstractTranslatorHelper {

    /**
     * Translate a message (string)
     *
     * @param  string $message string to be translated
     * @param  string $textDomain the text domain passed to translate method (always null (default))
     * @param  string $locale locale passed to translate method normally null since locale is defined in URI
     * @throws RuntimeException when translator has not been set
     * @return string the translated message
     */
    public function __invoke($message, $textDomain = null, $locale = null) {
        $translator = $this->getTranslator();
        if (null === $translator) {
            throw new RuntimeException('Translator has not been set');
        }
        if (null === $textDomain) {
            $textDomain = $this->getTranslatorTextDomain();
        }
        return $translator->translate($message, $textDomain, $locale);
    }

}
