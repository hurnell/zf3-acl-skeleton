<?php

/**
 * Class SocialController 
 *
 * @package     Social\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace Social\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Social\Service\SocialManager;
use AclUser\Entity\User;

/**
 * Class SocialController Handles requests pertaining to login and registration 
 * through social media providers
 *
 * @package     Social\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class SocialController extends AbstractActionController
{

    const ROUTE_REDIRECT = 'social';

    /**
     * Object where much of the (database) logic is handled
     * @var SocialManager
     */
    protected $socialManager;

    /**
     * Instantiate class and inject SocialManager
     * @param SocialManager $socialManager
     */
    public function __construct(SocialManager $socialManager)
    {
        $this->socialManager = $socialManager;
    }

    /**
     * Page where user is redirected if social login fails
     * 
     * @return ViewModel
     */
    public function failedLoginAction()
    {
        return new ViewModel();
    }

    /**
     * Action that is requested when user clicks on social auth image/link on 
     * login page (when login and/or registration is NOT enabled)
     * NOTE: Remove from AccessControlList if login and/or registration is enabled 
     * 
     * @return ViewModel
     */
    public function startLoginAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_LOGIN);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    /**
     * Action that is requested when user registration via social login is enabled
     * and user clicks on the image/link on the registration page
     * NOTE: Remove from AccessControlList if not enabled just to be sure
     * 
     * @return ViewModel
     */
    public function startRegistrationAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_REGISTRATION);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    /**
     * Action that is requested when user clicks on social auth image/link on 
     * login page (when login and/or registration IS enabled)
     * NOTE: Remove from AccessControlList if not enabled
     * 
     * @return ViewModel
     */
    public function startLoginOrRegistrationAction()
    {
        $url = $this->getRedirectUrl(SocialManager::SOCIAL_LOGIN_OR_REGISTRATION);
        if (false !== $url) {
            return $this->redirect()->toUrl($url);
        }
        return new ViewModel();
    }

    /**
     * Get the social providers entry URL
     * 
     * @param string $action defined in SocialManager as flag to choose logic flow 
     * @return false|string the redirect URL
     */
    protected function getRedirectUrl($action)
    {
        $url = false;
        $providerName = $this->params()->fromRoute('provider');
        $locale = $this->params()->fromRoute('locale');
        if (false !== $provider = $this->socialManager->startProvider($providerName)) {
            $this->socialManager->setAction($action);
            $this->socialManager->setlocale($locale);
            $callback = $this->getCallbackUrl($providerName);
            $url = $provider->getRedirectRoute($callback);
        }
        return $url;
    }

    /**
     * Get the URL which the user is redirected back to after the social provider 
     * authenticates their e-mail address
     * 
     * @param string $providerName
     * @return string 
     */
    public function getCallbackUrl($providerName)
    {
        return $this->url()->fromRoute(static::ROUTE_REDIRECT, ['locale' => 'en_GB', 'controller' => 'social', 'action' => 'redirected', 'provider' => $providerName], ['force_canonical' => true]);
    }

    /**
     * Action that is called when social provider redirects back to the 
     * application after validating user e-mail
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     */
    public function redirectedAction()
    {
        $locale = $this->socialManager->getlocale();
        $providerName = $this->params()->fromRoute('provider');
        $callback = $this->getCallbackUrl($providerName);
        $queryParams = $this->params()->fromQuery();
        $userProfile = 'no-provider';
        if (false !== $provider = $this->socialManager->startProvider($providerName)) {
            $userProfile = $provider->sendClientRequest($callback, $queryParams);
        }
        $result = $this->socialManager->handleSocialAuthRedirect($userProfile);
        $this->redirectMessage()->addRedirectMessages($result);
        if ($result->isValid()) {
            $this->redirect()->toRoute('default', ['locale' => $locale, 'controller' => 'index', 'action' => 'index']);
        } else {
            $this->redirect()->toRoute('default', ['locale' => $locale, 'controller' => 'social', 'action' => 'failed-login']);
        }
    }

}
