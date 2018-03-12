<?php

/**
 * Class RegistrationForm Creates the login form
 *
 * @package     AclUser\Form 
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * This form is used to create a new user.
 * 
 * @package     AclUser\Form 
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RegistrationForm extends Form
{

    /**
     * Constructor.   
     * 
     * @param boolean $withCaptcha whether captcha is needed
     */
    public function __construct($withCaptcha)
    {
        // Define form name
        parent::__construct('registration-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements($withCaptcha);
        $this->addInputFilter();
    }

    /**
     * This method adds elements to form (input fields and submit button).
     * @param boolean $withCaptcha whether captcha is needed
     */
    protected function addElements($withCaptcha)
    {

        // Add "new_password" field
        $this->add([
            'type' => 'text',
            'name' => 'full_name',
            'options' => [
                'label' => 'Full Name',
            ],
        ]);
        // Add "email" field
        $this->add([
            'type' => 'email',
            'name' => 'email',
            'options' => [
                'label' => 'E-mail',
            ],
        ]);
        // Add "password" field
        $this->add([
            'type' => 'password',
            'name' => 'password',
            'options' => [
                'label' => 'Password',
            ],
        ]);

        // Add "confirm_new_password" field
        $this->add([
            'type' => 'password',
            'name' => 'confirm_password',
            'options' => [
                'label' => 'Confirm  password',
            ],
        ]);
        if ($withCaptcha) {
            // Add the CAPTCHA field
            $this->add([
                'type' => 'captcha',
                'name' => 'captcha',
                'attributes' => [
                    'class' => 'captcha'
                ],
                'options' => [
                    'label' => 'Enter the letters above as you see them.',
                    'class' => 'captcha',
                    'captcha' => [
                        'class' => 'Image',
                        'imgDir' => 'public/img/captcha',
                        'suffix' => '.png',
                        'imgUrl' => '/img/captcha/',
                        'imgAlt' => 'CAPTCHA Image',
                        'font' => './data/font/thorne_shaded.ttf',
                        'fsize' => 24,
                        'width' => 350,
                        'height' => 100,
                        'expiration' => 600,
                        'dotNoiseLevel' => 40,
                        'lineNoiseLevel' => 3
                    ],
                ],
            ]);
        }
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

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Register',
                'id' => 'submit',
                'class' => 'btn btn-lg btn-primary btn-block'
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     */
    private function addInputFilter()
    {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        $inputFilter->add([
            'name' => 'full_name',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 1,
                        'max' => 64
                    ],
                ],
            ],
        ]);

        // Add input for "email" field
        $inputFilter->add([
            'name' => 'email',
            'required' => true,
            'filters' => [
                ['name' => 'StringTrim'],
            ],
            'validators' => [
                [
                    'name' => 'EmailAddress',
                    'options' => [
                        'allow' => \Zend\Validator\Hostname::ALLOW_DNS,
                        'useMxCheck' => false,
                    ],
                ],
            ],
        ]);

        $inputFilter->add([
            'name' => 'password',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'StringLength',
                    'options' => [
                        'min' => 6,
                        'max' => 64
                    ],
                ],
            ],
        ]);

        // Add input for "confirm_new_password" field
        $inputFilter->add([
            'name' => 'confirm_password',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'password',
                    ],
                ],
            ],
        ]);
    }

}
