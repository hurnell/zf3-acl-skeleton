<?php

/**
 * Class RedirectMessagePlugin
 *
 * @package     AclUser\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Mvc\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Authentication\Result;
use Zend\Uri\Uri;

/**
 * Controller plugin that redirects authentication requests and adds messages 
 * for to flash messenger
 * 
 * @package     AclUser\Mvc\Controller\Plugin
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class RedirectMessagePlugin extends AbstractPlugin
{

    /**
     * Add login messages to flash messenger according to what is contained in $result object
     * 
     * @param Zend\Authentication\Result $result
     */
    public function addRedirectMessages(Result $result)
    {

        $messenger = $this->controller->plugin('FlashMessenger');
        $messages = $result->getMessages();
        foreach ($messages as $namespace => $message) {
            $messenger->setNamespace($namespace)->addMessage($message);
        }
    }

    /**
     * Logic to handle possible login scenarios 
     * 
     * @param string $redirectUrl redirect route passed as query param in URL 
     */
    public function handleLoginRedirect($redirectUrl)
    {
        if (!empty($redirectUrl)) {
            // The below check is to prevent possible redirect attack 
            // (if someone tries to redirect user to another domain).
            $uri = new Uri($redirectUrl);
            if (!$uri->isValid() || $uri->getHost() != null) {
                $redirectUrl = '';
            }
        }
        // If redirect URL is provided, redirect the user to that URL;
        // otherwise redirect to Home page.
        if (empty($redirectUrl)) {
            $this->controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
        } else {
            $this->controller->redirect()->toUrl($redirectUrl);
        }
    }

    /**
     * Redirect user to home page if forgotten password post was valid
     * after adding flash messenger feedback.
     * 
     * @param Result $result
     */
    public function changePasswordRedirect(Result $result)
    {
        if ($result->isValid()) {
            $this->addRedirectMessages($result);
            $this->controller->redirect()->toRoute('default', ['controller' => 'index', 'action' => 'index']);
        }
    }

}
