<?php

/**
 * Class TranslationForm This is the form that actually saves or updates individual
 * message translations
 *
 * @package     Translate\Form
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Translate\Form;

use Zend\Form\Form;

//use Zend\InputFilter\InputFilter;
//use Zend\Validator\Hostname;

/**
 * Class TranslationForm
 *
 * @package     Translate\Form
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class TranslationForm extends Form {

    /**
     * Instantiate TranslationForm add elements filters and set method 
     */
    public function __construct() {
        // We will ignore the name provided to the constructor
        parent::__construct('translation');
        // Set POST method for this form
        $this->setAttribute('method', 'post');
        $this->addElements();
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     */
    protected function addElements() {
        $this->add([
            'name' => 'index',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'type',
            'type' => 'hidden',
        ]);

        $this->add([
            'name' => 'locale',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'idx',
            'type' => 'hidden',
        ]);
        $this->add([
            'name' => 'filepath',
            'type' => 'hidden',
        ]);
        // Add the CSRF field
        $this->add([
            'type' => 'csrf',
            'name' => 'csrf',
            'options' => [
                'csrf_options' => [
                    'timeout' => 600
                ]
            ],
        ]);
        $this->add([
            'name' => 'msgid',
            'type' => 'text',
            'options' => [
                'label' => 'Original',
            ],
        ]);
        $this->add([
            'name' => 'msgstr',
            'type' => 'text',
            'options' => [
                'label' => 'Translation',
            ],
        ]);
        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Go',
                'id' => 'submitbutton',
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter() {
        // Create main input filter
        // $inputFilter = new InputFilter();
        //   $this->setInputFilter($inputFilter);
        // Add input for "email" field
        /* $inputFilter->add([
          'name' => 'msgid',
          'required' => true,
          'filters' => [
          ['name' => 'StringTrim'],
          ],
          'validators' => [
          [
          'name' => 'EmailAddress',
          'options' => [
          'allow' => Hostname::ALLOW_DNS,
          'useMxCheck' => false,
          ],
          ],
          ],
          ]);// */
    }

}
