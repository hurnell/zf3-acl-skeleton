<?php

/**
 * Class ManageUsersController
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
use AclUser\Service\ManageUsersManager;
use AclUser\Form\RotateAndResizeImageForm;
use AclUser\Form\BasicProfileForm;
use Zend\Mvc\MvcEvent;

/**
 * This controller is responsible for user management (adding, editing, 
 * viewing users and changing user's password).
 * 
 * @package     AclUser\Controller
 * @author      Nigel Hurnell
 * @version     v.1.0.0
 * @license     BSD
 * @copyright   Copyright (c) 2017, Nigel Hurnell
 */
class ManageUsersController extends AbstractActionController
{

    /**
     * Instance that renders views
     * 
     * @var Zend\View\Renderer\PhpRenderer 
     */
    private $viewRenderer;

    /**
     * ManageUsersManager Service handles logic for this controller
     * 
     * @var  ManageUsersManager
     */
    protected $manageUsersManager;

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
     * @param ManageUsersManager $userManager
     */
    public function __construct(ManageUsersManager $userManager)
    {
        $this->manageUsersManager = $userManager;
    }

    /**
     * Get list of all registered users
     * 
     * @return ViewModel
     */
    public function listUsersAction()
    {
        $users = $this->manageUsersManager->getAllUsers();
        return new ViewModel([
            'users' => $users
        ]);
    }

    /**
     * Manage particular user's roles
     * 
     * @return ViewModel
     */
    public function manageUserRolesAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $user = $this->manageUsersManager->findUserById($id);
        if (!isset($user)) {
            return $this->redirect()->toRoute('default', ['controller' => 'manage-users', 'action' => 'list-users']);
        }
        list($userRoles, $possibleRoles) = $this->manageUsersManager->getRolesByUser($user);
        return new ViewModel(array(
            'user' => $user,
            'userRoles' => $userRoles,
            'possibleRoles' => $possibleRoles
        ));
    }

    /**
     * Send photo upload form in view script as JSON
     * 
     * @return JsonModel
     */
    public function ajaxGetPhotoUploadFormAction()
    {
        $postFormUrl = $this->url()->fromRoute('manage-users',
                ['action' => 'ajax-receive-user-photo-file', 'id' => $this->params()->fromRoute('id', 0)]);
        $viewModel = new ViewModel(['url' => $postFormUrl]);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('acl-user/user/ajax-get-photo-upload-form');
        return new JsonModel(['view' => $this->viewRenderer->render($viewModel)]);
    }

    /**
     * Ajax action to which user's photo file object is posted
     * 
     * @return JsonModel
     */
    public function ajaxReceiveUserPhotoFileAction()
    {
        $form = new RotateAndResizeImageForm('image-rotate-and-resize-form');
        $result = $this->manageUsersManager->validatePhotoUploadForm(
                $this->getRequest()->isPost(),
                $form,
                $this->params(),
                $this->params()->fromRoute('id')
        );

        return new JsonModel([
            'success' => $result['success'],
            'errors' => $this->manageUsersManager->getTranslatedErrorMesssages($this->translateContollerPlugin(), $result['errors'])
        ]);
    }

    /**
     * Go to edit user's profile page
     * 
     * @return ViewModel
     */
    public function editProfileAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $user = $this->manageUsersManager->findUserById($id);
        if (!isset($user)) {
            return $this->redirect()->toRoute('default', ['controller' => 'manage-users', 'action' => 'list-users']);
        }
        return new ViewModel([
            'user' => $user,
            'controller' => 'manage-users'
        ]);
    }

    /**
     * Add or remove role from user (ajax)
     * 
     * @return JsonModel
     */
    public function ajaxUpdateUserRoleMembershipAction()
    {
        $userId = (int) $this->params()->fromPost('user_id', 0);
        $roleId = (int) $this->params()->fromPost('role_id', 0);
        $type = $this->params()->fromPost('type', 'none');
        $this->manageUsersManager->updateUserRoleMembership($type, $userId, $roleId);
        return new JsonModel(array(
            'return' => 'nothing needed'
        ));
    }

    /**
     * Toggle whether user is active or suspended
     * 
     * @return JsonModel
     */
    public function ajaxToggleSuspensionUserByIdAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $success = $this->manageUsersManager->toggleSuspensionUserById($id);
        return new JsonModel(array(
            'success' => $success
        ));
    }

    /**
     * Delete registered user
     * 
     * @return JsonModel
     */
    public function ajaxDeleteUserByIdAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $success = $this->manageUsersManager->deleteUserById($id);
        return new JsonModel(array(
            'success' => $success
        ));
    }

    /**
     * Get profile form page
     * 
     * @return JsonModel
     */
    public function ajaxGetBasicProfileFormAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $form = new BasicProfileForm();
        if (!$this->manageUsersManager->prepopulateUserProfile($form, $id)) {
            return $this->redirect()->toRoute('default', ['controller' => 'manage-users', 'action' => 'list-users']);
        }
        $success = false;
        if ($this->getRequest()->isPost()) {
            $success = $this->manageUsersManager->validateBasicProfileForm(
                    $form,
                    $this->params()->fromPost(),
                    $id
            );
        }

        $formAction = $this->url()->fromRoute('manage-users',
                ['action' => 'ajax-get-basic-profile-form', 'id' => $id]);
        $viewModel = new ViewModel(['form' => $form, 'action' => $formAction]);
        $viewModel->setTerminal(true);
        $viewModel->setTemplate('acl-user/user/ajax-get-basic-profile-form');
        return new JsonModel([
            'view' => $this->viewRenderer->render($viewModel),
            'success' => $success
        ]);
    }

}
