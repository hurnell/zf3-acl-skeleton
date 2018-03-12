<?php

/**
 * Class UserController
 *
 * @package     AclUser\Controller
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */

namespace AclUser\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use AclUser\Service\UserManager;
use AclUser\Form\RotateAndResizeImageForm;
use AclUser\Form\ForgottenPasswordForm;
use AclUser\Form\BasicProfileForm;
use Zend\Mvc\MvcEvent;
use Doctrine\ORM\EntityManager;

/**
 * Class UserController
 * 
 * Handles basic user requests
 * 
 * @package     AclUser\Controller
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class UserController extends AbstractActionController
{

    /**
     * Entity manager.
     * @var EntityManager
     */
    private $entityManager;

    /**
     * User manager.
     * @var UserManager 
     */
    private $userManager;

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
     * Instantiate controller class with injected resources
     * 
     * @param EntityManager $entityManager
     * @param UserManager $userManager
     */
    public function __construct(EntityManager $entityManager, UserManager $userManager)
    {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
    }

    /**
     * Landing page where authenticated user can update their profile
     * 
     * @uses AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::getPresentUser()
     * @return ViewModel
     */
    public function profileAction()
    {
        $user = $this->aclControllerPlugin()->getPresentUser();
        return new ViewModel([
            'user' => $user,
            'controller' => 'user'
        ]);
    }

    /**
     * This action displays the "Forgotten Password" page.
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\RedirectMessagePlugin::addRedirectMessages() 
     * @return ViewModel
     */
    public function forgottenPasswordAction()
    {
        // Create form
        $form = new ForgottenPasswordForm();

        //Check if user has submitted the form
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $params = $this->params()->fromPost();
            $result = $this->userManager->validateForgottenPasswordForm($form, $params);
            if ($result->isValid()) {
                $this->redirectMessage()->addRedirectMessages($result);
                $this->redirect()->toRoute('default', ['controller' => 'user', 'action' => 'forgotten-password']);
            }
        }

        return new ViewModel([
            'form' => $form
        ]);
    }

    /**
     * Serve raw response of user's photo
     * 
     * @uses \Application\Mvc\Controller\Plugin\RawResponsePlugin::serveImage()
     * @return Response user photo data
     */
    public function serveUserPhotoAction()
    {
        $userId = (int) $this->params()->fromRoute('id', 0);
        $permitted = $this->userHasControlOverThisImage($userId);
        $filepath = $this->userManager->getUserPhotoLocationById($userId, $permitted);
        return $this->rawResponse()->serveImage($filepath);
    }

    /**
     * Check whether user ($userId) is permitted to view/update the image
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::getPresentUserId()
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::userIsAllowed()
     * @param int $userId the id of the photo/user
     * @return boolean Whether user has permission
     */
    protected function userHasControlOverThisImage($userId)
    {
        return $this->aclControllerPlugin()->getPresentUserId() == $userId || $this->aclControllerPlugin()->userIsAllowed('manage-users', 'can-access-all-user-photos');
    }

    /**
     * Send photo upload form in view script as JSON
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::getPresentUserId()
     * @return JsonModel
     */
    public function ajaxGetPhotoUploadFormAction()
    {
        /* this user can only access the form for themself */
        $id = $this->aclControllerPlugin()->getPresentUserId();
        $postFormUrl = $this->url()->fromRoute('user-id',
                ['action' => 'ajax-receive-user-photo-file', 'id' => $id]);
        $viewModel = new ViewModel(['url' => $postFormUrl]);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('acl-user/user/ajax-get-photo-upload-form');
        return new JsonModel(['view' => $this->viewRenderer->render($viewModel)]);
    }

    /**
     * Ajax action to which user's photo file object is posted
     * 
     * @uses \AclUser\Mvc\Controller\Plugin\UserIsAllowedControllerPlugin::getPresentUserId()
     * @return JsonModel
     */
    public function ajaxReceiveUserPhotoFileAction()
    {
        $form = new RotateAndResizeImageForm();
        $result = $this->userManager->validatePhotoUploadForm(
                $this->getRequest()->isPost(),
                $form,
                $this->params(),
                $this->aclControllerPlugin()->getPresentUserId()
        );

        return new JsonModel([
            'success' => $result['success'],
            'errors' => $this->userManager->getTranslatedErrorMesssages($this->translateContollerPlugin(), $result['errors']),
        ]);
    }

    /**
     * Get profile form page
     * 
     * @return JsonModel
     */
    public function ajaxGetBasicProfileFormAction()
    {
        /* this user can only access the form for themself */
        $id = $this->aclControllerPlugin()->getPresentUserId();
        $form = new BasicProfileForm();
        $this->userManager->prepopulateUserProfile($form, $id);
        $success = false;
        if ($this->getRequest()->isPost()) {
            $success = $this->userManager->validateBasicProfileForm(
                    $form,
                    $this->params()->fromPost(),
                    $id
            );
        }
        $formAction = $this->url()->fromRoute('user-id',
                ['action' => 'ajax-get-basic-profile-form', 'id' => $id]);
        $viewModel = new ViewModel(['form' => $form, 'action' => $formAction]);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('acl-user/user/ajax-get-basic-profile-form');
        return new JsonModel(['view' => $this->viewRenderer->render($viewModel), 'success' => $success]);
    }

}
