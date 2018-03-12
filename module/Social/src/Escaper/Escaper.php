<?php

/**
 * Do not escape URL path for LinkedIn
 *
 * @package     Social\Escaper
 * @author      Nigel Hurnell
 * @version     v.0.0.1
 * @license     BSD
 * @uses        Zend Framework 3
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace Social\Escaper;

use Zend\Escaper\Escaper as ZendEscaper;

/**
 * Class Escaper 
 *
 * @package     Social\Escaper
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class Escaper extends ZendEscaper
{

    /**
     * Leave the URL as it is /v1/people/~:(id,email-address,formatted-name)
     * Do not escape the brackets
     * 
     * @param string $string
     * @return string
     */
    public function escapeUrl($string)
    {
        return ($string);
    }

}
