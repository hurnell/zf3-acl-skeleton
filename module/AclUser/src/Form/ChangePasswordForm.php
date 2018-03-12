<?php

/**
 * Class ChangePasswordForm
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
 * This form allows a authenticated user to change their password
 * 
 * @package     AclUser\Form 
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ChangePasswordForm extends Form {

    /**
     * Constructor.     
     * 
     * @param boolean $withOldPassword whether old password field is needed
     */
    public function __construct($withOldPassword) {
        // Define form name
        parent::__construct('change-password-form');

        // Set POST method for this form
        $this->setAttribute('method', 'post');

        $this->addElements($withOldPassword);
        $this->addInputFilter($withOldPassword);
    }

    /**
     * This method adds elements to form (input fields and submit button).
     * 
     * @param boolean $withOldPassword whether old password field is needed
     */
    protected function addElements($withOldPassword) {
        if ($withOldPassword) {
            // Add "old_password" field
            $this->add([
                'type' => 'password',
                'name' => 'old_password',
                'options' => [
                    'label' => 'Old Password',
                ],
            ]);
        }

        // Add "new_password" field
        $this->add([
            'type' => 'password',
            'name' => 'new_password',
            'options' => [
                'label' => 'New Password',
            ],
        ]);

        // Add "confirm_new_password" field
        $this->add([
            'type' => 'password',
            'name' => 'confirm_new_password',
            'options' => [
                'label' => 'Confirm new password',
            ],
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

        // Add the Submit button
        $this->add([
            'type' => 'submit',
            'name' => 'submit',
            'attributes' => [
                'value' => 'Change Password',
                'id' => 'submit',
            ],
        ]);
    }

    /**
     * This method creates input filter (used for form filtering/validation).
     * 
     * @param boolean $withOldPassword whether old password field is needed
     */
    private function addInputFilter($withOldPassword) {
        // Create main input filter
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        if ($withOldPassword) {
            $inputFilter->add([
                'name' => 'old_password',
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
        }
        $inputFilter->add([
            'name' => 'new_password',
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
            'name' => 'confirm_new_password',
            'required' => true,
            'filters' => [
            ],
            'validators' => [
                [
                    'name' => 'Identical',
                    'options' => [
                        'token' => 'new_password',
                    ],
                ],
            ],
        ]);
    }

}
