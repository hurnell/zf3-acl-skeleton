<?php

/**
 * Class TestAccessControlList 
 *
 * @package     AclUserTest\Mocked
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */

namespace AclUserTest\Mocked;

/**
 * This class is used stop application making database calls when the application is under test
 *
 * @package     AclUserTest\Mocked
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2018, Nigel Hurnell
 */
class TestAccessControlList
{

    /**
     * Mocked onDispatch event listener hook
     * 
     * @param MvcEvent $event
     */
    public function onDispatch($event)
    {
        
    }

}
