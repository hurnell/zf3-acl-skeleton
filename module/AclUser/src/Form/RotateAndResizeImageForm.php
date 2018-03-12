<?php

/**
 * Class AclUser\Form
 *
 * @package     AclUser\Form
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilter;

/**
 * Creates the RotateAndResizeImageForm which is used to check all parameters 
 * are available and move uploaded image file to its initial location
 * 
 * @package     AclUser\Form
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RotateAndResizeImageForm extends Form
{

    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        parent::__construct('image-rotate-and-resize-form');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->addElements();
    }

    /**
     * Add form elements to the form
     */
    protected function addElements()
    {
        $this->add([
            'type' => 'file',
            'name' => 'file',
            'attributes' => [
                'id' => 'file'
            ],
            'options' => [
                'label' => 'Image file',
            ],
        ]);
        $textElements = ['file-name', 'dst_x', 'dst_y', 'src_x', 'src_y', 'dst_w', 'dst_h', 'src_w', 'src_h', 'rotation', 'ratio'];
        foreach ($textElements as $name) {
            $this->add([
                'type' => 'text',
                'name' => $name,
            ]);
        }
        $this->addInputFilter();
    }

    /**
     * Add input filter to the form
     * Input filters are used to validate posted values
     */
    private function addInputFilter()
    {
        $inputFilter = new InputFilter();
        $this->setInputFilter($inputFilter);
        $inputFilter->add([
            'type' => 'Zend\InputFilter\FileInput',
            'name' => 'file',
            'required' => true,
            'validators' => [
                ['name' => 'FileUploadFile'],
                ['name' => 'FileIsImage'],
                [
                    'name' => 'FileImageSize',
                    'options' => [
                        'minWidth' => 50,
                        'minHeight' => 50,
                        'maxWidth' => 4096,
                        'maxHeight' => 4096
                    ]
                ],
            ],
            'filters' => [
                [
                    'name' => 'FileRenameUpload',
                    'options' => [
                        'target' => './data/media/upload',
                        'useUploadName' => true,
                        'useUploadExtension' => true,
                        'overwrite' => true,
                        'randomize' => false
                    ]
                ]
            ],
        ]);
    }

}
