<?php

/**
 * Class TranslationController
 *
 * @package     Translate\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Translate\Service\TranslationManager;
use Translate\Service\LanguageManager;
use Translate\Form\TranslationForm;

/**
 * Class TranslationController
 * 
 * Handles basic requests pertaining to the translation module
 *
 * @package     Translate\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationController extends AbstractActionController
{

    /**
     * Service that handles basic translation model logic
     * 
     * @var TranslationManager;
     */
    protected $translationManager;

    /**
     * Service that handles locale model logic
     * 
     * @var LanguageManager;
     */
    protected $languageManager;

    /**
     * Instantiate class with injected resources
     * 
     * @param TranslationManager $translationManager service that handles basic translation logic
     * @param LanguageManager $languageManager
     */
    public function __construct(TranslationManager $translationManager, LanguageManager $languageManager)
    {
        $this->translationManager = $translationManager;
        $this->languageManager = $languageManager;
    }

    /**
     * This is the default "index" action of the controller. It displays the 
     * Home page.
     */
    public function indexAction()
    {
        return new ViewModel([
            'locales' => $this->translationManager->getLocalesWithTranslations()
        ]);
    }

    /**
     * Display basic language lists
     * actual logic is performed in jQuery file
     * 
     * @return ViewModel
     */
    public function manageSystemLanguagesAction()
    {
        return new ViewModel($this->languageManager->getLanguagesArray());
    }

    /**
     * This page displays all (or untranslated) messages for the chosen language
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::userIsAllowed()
     * @return ViewModel
     */
    public function editAction()
    {

        $type = $this->params()->fromRoute('type', 'all');
        $locale = $this->params()->fromRoute('language', 'false');

        if (!$this->aclControllerPlugin()->userIsAllowed('translate', $locale)) {
            return $this->redirect()->toRoute('default', ['controller' => 'translate', 'action' => 'index']);
        }
        return new ViewModel([
            'translations' => $this->translationManager->getAllTranslations($type, $locale),
            'locale' => $locale,
            'type' => $type
        ]);
    }

    /**
     * This page displays the form where a particular message can be translated
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::userIsAllowed()
     * @return ViewModel
     */
    public function editTranslationAction()
    {
        $type = $this->params()->fromRoute('type', false);
        $locale = $this->params()->fromRoute('language', false);

        if (!$this->aclControllerPlugin()->userIsAllowed('translate', $locale)) {
            return $this->redirect()->toRoute('default', ['controller' => 'translate', 'action' => 'index']);
        }
        // create translation form
        $form = new TranslationForm();
        if ($this->getRequest()->isPost()) {
            // get  POST data
            $data = $this->params()->fromPost();
            // populate form with POST data
            $form->setData($data);

            // Validate form
            if ($form->isValid()) {

                // Get filtered and validated data
                $data = $form->getData();

                // Update translation.
                $this->translationManager->updateTranslation($data);

                // Redirect to "view" page
                return $this->redirect()->toRoute('translate', ['action' => 'edit', 'language' => $data['locale'], 'type' => 'all']);
            }
        }
        $idx = $this->params()->fromRoute('idx', false);
        $index = $this->params()->fromRoute('index', false);
        $data = $this->translationManager->getTranslationArray($type, $locale, $idx, $index);
        $lang = 'unknown language';
        $msgId = 'unknown message id';
        if (is_array($data)) {
            $data['type'] = $type;
            $data['locale'] = $locale;
            $parts = explode('_', $locale);
            $lang = $parts[0];
            $msgId = urlencode($data['msgid']);
            $form->setData($data);
        }
        return new ViewModel([
            'form' => $form,
            'locale' => $locale,
            'lang' => $lang,
            'msgid' => $msgId
        ]);
    }

    /**
     * Handle logic when flag is dragged between available languages and
     * Enabled languages on manage-system-languages page
     * 
     * @return JsonModel
     */
    public function ajaxUpdateAvailableLanguagesAction()
    {
        $language = $this->params()->fromPost('locale');
        $change = $this->params()->fromPost('change');
        $result = $this->languageManager->toggleLanguage($language, $change);
        return new JsonModel([
            'result' => $result
        ]);
    }

}
