<?php

/**
 * Class AuthController
 *
 * @package     AclUser\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use AclUser\Service\UserManager;
use AclUser\Service\AuthManager;
use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService;
use AclUser\Form\LoginForm;
use Zend\Authentication\Result;
use AclUser\Form\ChangePasswordForm;
use AclUser\Form\ResetPasswordForm;
use AclUser\Form\RegistrationForm;
use Zend\Mvc\MvcEvent;

/**
 * Class AuthController
 * 
 * Handles basic login/logout requests
 * 
 * @package     AclUser\Controller
 * @author      Nigel Hurnell
 * @version     v 1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class AuthController extends AbstractActionController
{

    /**
     * Doctrine entity manager
     * 
     * @var EntityManager 
     */
    protected $entityManager;

    /**
     * The AuthManager service that handles logic for this controller
     * 
     * @var AuthManager 
     */
    protected $authManager;

    /**
     * Zend\Authentication\AuthenticationService which handles session persistence 
     * for authenticated user etc.
     * 
     * @var AuthenticationService 
     */
    protected $authService;

    /**
     * UserManager Service handles logic related to authorised user
     * 
     * @var UserManager 
     */
    protected $userManager;

    /**
     * Instance that renders views
     * 
     * @var Zend\View\Renderer\PhpRenderer 
     */
    private $viewRenderer;

    /**
     * Intercept on dispatch event to get view renderer
     * @param MvcEvent $e
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->viewRenderer = $e->getApplication()->getServiceManager()->get('ViewRenderer');
        parent::onDispatch($e);
    }

    /**
     * Instantiate class with injected resources
     * 
     * @param EntityManager $entityManager Doctrine entity manager
     * @param AuthManager $authManager where the 
     * @param AuthenticationService $authService
     * @param UserManager $userManager
     */
    public function __construct(EntityManager $entityManager, AuthManager $authManager, AuthenticationService $authService, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->authManager = $authManager;
        $this->authService = $authService;
        $this->userManager = $userManager;
    }

    /**
     * Handle login request
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages()
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::handleLoginRedirect()
     * @return ViewModel
     * @throws \Exception
     */
    public function loginAction()
    {
        $redirectUrl = (string) $this->params()->fromQuery('redirectUrl', '');
        if (strlen($redirectUrl) > 2048) {
            throw new \Exception("Too long redirectUrl argument passed");
        }
        /* gcreate new login form */
        $form = new LoginForm();
        $form->get('redirect_url')->setValue($redirectUrl);
        if ($this->getRequest()->isPost()) {
            $params = $this->params()->fromPost();
            /* if user has submitted login form */
            $result = $this->authManager->validateLoginForm($form, $params);
            if ($result->getCode() !== Result::FAILURE_UNCATEGORIZED) {
                $this->redirectMessage()->addRedirectMessages($result);
                $this->redirectMessage()->handleLoginRedirect($this->params()->fromPost('redirect_url', ''));
            }
        }
        return new ViewModel([
            'form' => $form,
        ]);
    }

    /**
     * Log user out, add confirmation message and redirect back to login page
     * @return Zend\Http\PhpEnvironment\Response
     */
    public function logoutAction()
    {
        $this->authManager->logout();
        $this->flashMessenger()->setNamespace('success')->addMessage('You have been logged out.');
        return $this->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'login']);
    }

    /**
     * Allow new user to register with the system
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function registerAction()
    {
        $withCaptcha = true;
        $form = new RegistrationForm($withCaptcha);
        if ($this->getRequest()->isPost()) {
            $result = $this->userManager->validateRegistrationForm($form, $this->params()->fromPost(), $withCaptcha);
            if ($result->isValid()) {
                $this->redirectMessage()->addRedirectMessages($result);
                $this->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'register']);
            }
        }
        return new ViewModel([
            'form' => $form,
            'withCaptcha' => $withCaptcha,
        ]);
    }

    /**
     * Administrator creates a new user
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function createNewUserAction()
    {
        $withCaptcha = false;
        $form = new RegistrationForm($withCaptcha);
        if ($this->getRequest()->isPost()) {
            $result = $this->userManager->validateRegistrationForm($form, $this->params()->fromPost(), $withCaptcha);
            if ($result->isValid()) {
                $this->redirectMessage()->addRedirectMessages($result);
                $this->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'create-new-user']);
            }
        }
        $view = new ViewModel([
            'form' => $form,
            'withCaptcha' => $withCaptcha,
        ]);
        $view->setTemplate('acl-user/auth/register');
        return $view;
    }

    /**
     * Confirm that user has registered with own email
     * Change user status from retired to active
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function confirmAccountAction()
    {
        $token = $this->params()->fromRoute('token', 'expired');

        if ($token != 'expired') {
            $result = $this->userManager->activateAccountByToken($token);
            $this->redirectMessage()->addRedirectMessages($result);
            if ($result->isValid()) {
                $this->redirect()->toRoute('default', ['controller' => 'user-auth', 'action' => 'login']);
            } else {
                $this->redirect()->toRoute('send-token', ['controller' => 'user-auth', 'action' => 'confirm-account', 'token' => 'expired']);
            }
        }
        return new ViewModel();
    }

    /**
     * Reset password after user has received e-mail after  
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function resetPasswordAction()
    {
        // Create form
        $form = new ResetPasswordForm();
        $token = $this->params()->fromRoute('token');
        $showForm = true;
        if ($token == 'failure') {
            $showForm = false;
        } else if ($this->getRequest()->isGet()) {
            $result = $this->userManager->checkResetToken($token);
            if (!$result->isValid()) {
                $this->redirectMessage()->addRedirectMessages($result);
                return $this->redirect()->toRoute('send-token', ['token' => 'failure', 'locale' => $this->params()->fromRoute('locale')]);
            }
            $form->populateValues(['token' => $token]);
        } else if ($this->getRequest()->isPost()) {
            //Check if user has submitted the form
            $result = $this->userManager->validateResetPasswordForm($form, $this->params()->fromPost());
            if ($result->isValid()) {
                $this->authManager->loginUser($result->getIdentity());
                $this->redirectMessage()->addRedirectMessages($result);
                $this->redirect()->toRoute('default', ['locale' => $this->params()->fromRoute('locale'), 'controller' => 'index', 'action' => 'index']);
            }
        }
        return new ViewModel([
            'form' => $form,
            'showForm' => $showForm
        ]);
    }

    /**
     * Change password for logged in users
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::getPresentUser()
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function changePasswordAction()
    {
        $user = $this->aclControllerPlugin()->getPresentUser();
        //short passwords indicate social registration without hashed password
        $withOldPassword = strlen($user->getPassword()) > 25;
        // Create form
        $form = new ChangePasswordForm($withOldPassword);
        //Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            $result = $this->userManager->validateChangePasswordForm($form, $this->params()->fromPost(), $user);
            //add flash messenger messages and redicect if password checnge form was valid.
            $this->redirectMessage()->changePasswordRedirect($result);
        }
        return new ViewModel([
            'form' => $form,
            'withOldPassword' => $withOldPassword
        ]);
    }

}
