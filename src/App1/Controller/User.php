<?php
/**
 * Description of App1\Controller\User
 *
 * @author Pierre Fromager
 */
namespace App1\Controller;

use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Flash as flashTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Tools\User\Auth as authTools;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use App1\Form\Users\Search as searchUsersForm;
use App1\Form\Users\Edit as editUsersForm;
use App1\Form\Users\Password as passwordForm;
use App1\Form\Users\Lostpassword as lostPasswordForm;
use App1\Form\Users\Login as loginForm;
use App1\Form\Users\Register as registerForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Tools\Mail\Sender as mailSender;
use App1\Helper\Controller\User as helperUserController;

final class User extends helperUserController
{

    /**
     * user
     *
     * @return \Pimvc\Http\Response
     */
    final public function index()
    {
        $input = $this->getIndexInputFilter();
        $transform = new \stdClass();
        $transform->filter = $input->get();
        $transform->data = $this->userModel->find(
            [self::_ID, self::_EMAIL],
            [
                self::_ID . '#>' => (isset($input->id)) ? $input->id : 800
                , self::_EMAIL => (isset($input->email)) ? self::WILDCARD . $input->email . self::WILDCARD : self::WILDCARD
                ]
        )->getRowset();
        unset($input);
        return $this->getJsonResponse($transform);
    }

    /**
     * login
     *
     * @return string
     */
    final public function login()
    {
        $request = $this->getApp()->getRequest();
        $postedData = $request->get()[$request::REQUEST_P_REQUEST];
        $inputLoginFilter = $this->getLoginInputFilter($postedData);
        $form = new loginForm((array) $inputLoginFilter);
        if ($inputLoginFilter->login && $inputLoginFilter->password || $inputLoginFilter->token) {
            $auth = new authTools(
                $inputLoginFilter->login,
                $inputLoginFilter->password,
                $inputLoginFilter->token
            );
            if ($auth->isAllowed) {
                $authAction = ($auth->profil === 'admin') ? 'user/manage' : 'user/edit';
                return $this->redirect($this->baseUrl . '/' . $authAction);
            } else {
                flashTools::addError('Authentication failed');
                return $this->redirect($this->baseUrl . '/home');
            }
        }
        $view = $this->getView(
            ['form' => (string) $form],
            self::VIEW_USER_PATH . ucfirst(__FUNCTION__) . self::PHP_EXT
        );
        unset($form);
        $widget = $this->getWidget(
            '<span class="fa fa-sign-in"></span>Login' . $this->getLoginLinks(),
            (string) $view
        );
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * logoutAction
     *
     * @return array
     */
    final public function logout()
    {
        sessionTools::deAuth();
        flashTools::addInfo(self::USER_MESSAGE_DISCONECTED);
        return $this->redirect($this->baseUrl . '/user/login');
    }

    /**
     * register
     *
     * @return Response
     */
    final public function register()
    {
        $message = '';
        $request = $this->getApp()->getRequest();
        $postedData = $request->get()[$request::REQUEST_P_REQUEST];
        $form = new registerForm($postedData);
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
        if ($isPost) {
            if ($form->isValid()) {
                $userExists = $this->userModel->userExists($this->getParams(self::_LOGIN));
                if (!$userExists) {
                    $hasError = $this->createUser();
                    $messageType = ($hasError) ? 'error' : 'info';
                    $message = ($hasError) ? $this->userModel->getError() : self::USER_MESSAGE_REGISTRATION_SUCCESS;
                    flashTools::add($messageType, $message);
                    $redirectAction = ($hasError) ? 'user/register/type/' . $this->getParams(self::_PROFIL) : 'user/login';
                    $redirectUrl = $this->baseUrl . '/' . $redirectAction;
                    return $this->redirect($redirectUrl);
                } else {
                    flashTools::addError(self::USER_MESSAGE_REGISTRATION_FAILED);
                }
            } else {
                $errorMessages = $form->getErrors();
                foreach ($errorMessages as $key => $value) {
                    flashTools::addWarning($key . ' : ' . $value);
                }
            }
        }
        $widget = $this->getWidget('Register', (string) $form);
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $this->setPageSize();
        $criterias = $this->getAssist();
        $form = new searchUsersForm($criterias);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $liste = new \Pimvc\Liste(
            get_class($this->userModel),
            'user/manage',
            array_diff(
                $this->userModel->getDomainInstance()->getVars(),
                [self::_ID, self::_NAME, self::_LOGIN, self::_STATUS]
            ),
            [
            glyphToolbar::EXCLUDE_DETAIL => false
            , glyphToolbar::EXCLUDE_IMPORT => true
            , glyphToolbar::EXCLUDE_NEWSLETTER => true
            , glyphToolbar::EXCLUDE_PDF => true
            , glyphToolbar::EXCLUDE_CLONE => false
            , glyphToolbar::EXCLUDE_PEOPLE => true
            , glyphToolbar::EXCLUDE_REFUSE => true
            ],
            $this->getParams('page'),
            $criterias,
            [],
            [self::_ORDER => 'desc']
        );
        $liste->setActionCondition([
            glyphToolbar::EXCLUDE_VALIDATE => [
                'key' => self::_STATUS, 'value' => 'valid'
            ]
        ]);
        $liste->setLabels(editUsersForm::_getStaticLabels($withIcons = false));
        $liste->setFormater(self::_PROFIL, 'Helper_Format_Roles::getFliped');
        if ($this->hasValue('context')) {
            return $this->getJsonResponse($liste->getJson());
        }
        $liste->render();
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::SEARCH)
            . 'Gestion des comptes utilisateurs',
            $filter . $this->getListeTableResponsive($liste)
        );
        unset($liste);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * duplicate
     *
     * return Response
     */
    final public function duplicate()
    {
        if ($id = $this->getParams(self::_ID)) {
            $userObject = $this->userModel->getById($id);
            unset($userObject->id);
            $this->userModel->save($userObject);
            flashTools::addInfo('User id ' . $id . ' dupliqué.');
            $redirectUrl = $this->baseUrl . '/' . self::LIST_ACTION;
            return $this->redirect($redirectUrl);
        }
        $this->getError();
    }

    /**
     * editAction
     *
     * @return Response
     */
    final public function edit()
    {
        $message = '';
        $uid = ($this->hasValue(self::_ID)) ? $this->getParams(self::_ID) : sessionTools::getUid();
        $this->userModel->cleanRowset();
        $isAdmin = sessionTools::isAdmin();
        $postedDatas = ($isPost = $this->isPost()) ? $this->getParams() : (array) $this->userModel->getById($uid);
        if (isset($postedDatas[self::_EMAIL])) {
            $postedDatas[self::_EMAIL] = strtolower($postedDatas[self::_EMAIL]);
        }
        $form = new editUsersForm($postedDatas, $uid, $mode = '');
        if ($isPost) {
            if ($form->isValid()) {
                if (!$isAdmin) {
                    $integ = $this->checkIntegrity($postedDatas);
                    if (!$integ) {
                        $message = 'Vous ne pouvez pas modifier des informations'
                            . ' qui ne vous appartiennent pas.';
                        flashTools::addError($message);
                        return $this->redirect($this->baseUrl . '/user/detail');
                    }
                }
                if (isset($postedDatas[\Pimvc\Form::FORM_XCSRF])) {
                    die;
                    unset($postedDatas[\Pimvc\Form::FORM_XCSRF]);
                }
                $postedDatas[self::_TOKEN] = \Pimvc\Tools\User\Token::get(
                    $postedDatas[self::_EMAIL],
                    $postedDatas['password']
                );
                $postedDatas['ip'] = $this->getApp()->getRequest()->getRemoteAddr();
                $domainInstance = $this->userModel->getDomainInstance();
                $domainInstance->hydrate($postedDatas);
                $this->userModel->saveDiff($domainInstance);
                unset($domainInstance);
                $hasError = $this->userModel->hasError();
                if ($hasError) {
                    $message = self::USER_MESSAGE_ERROR . $this->_model->getError();
                    return array(self::PARAM_CONTENT => $message);
                } else {
                    $redirectÀction = ($isAdmin) ? self::LIST_ACTION : self::DETAIL_ACTION;
                    flashTools::addInfo(self::USER_MESSAGE_VALDATED);
                    return $this->redirect($this->baseUrl . '/' . $redirectÀction);
                }
            } else {
                $formErrors = $form->getErrors();
                foreach ($formErrors as $k => $v) {
                    flashTools::addError($v);
                }
                $message = (string) $form;
            }
        } else {
            $message = (string) $form;
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::PENCIL)
            . 'Edition du compte' . $this->getEditLinks($uid),
            (string) $message
        );
        unset($form);
        unset($message);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * detailAction
     *
     * @return Response
     */
    final public function detail()
    {
        $message = 'Voici vos informations de compte,'
            . ' pour les modifier cliquez sur &nbsp;'
            . glyphHelper::get(glyphHelper::PENCIL) . '.';
        flashTools::addInfo($message);
        $uid = ($this->hasValue(self::_ID)) ? $this->getParams(self::_ID) : sessionTools::getUid();
        $this->userModel->cleanRowset();
        $formDatas = (array) $this->userModel->getById($uid);
        $form = new editUsersForm($formDatas, $uid, 'readonly');
        $form->setEnableButtons(false);
        $form->render();
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::EYE_OPEN)
            . 'Détail du compte' . $this->getDetailLinks(),
            (string) $form
        );
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * deleteAction
     *
     * @return array
     */
    final public function delete()
    {
        if ($this->hasValue(self::_ID)) {
            $this->userModel->cleanRowset();
            $where = [self::_ID => $this->getParams(self::_ID)];
            $this->userModel->setWhere($where);
            $this->userModel->bindWhere();
            $this->userModel->delete();
            $hasError = $this->userModel->hasError();
            $messageType = ($hasError) ? 'error' : 'info';
            $message = ($hasError) ? self::USER_MESSAGE_DELETE_ERROR . $this->userModel->getError() : self::USER_MESSAGE_DELETE_SUCCESS;
            flashTools::add($messageType, $message);
            return $this->redirect($this->baseUrl . '/' . self::LIST_ACTION);
        }
        $this->getError();
    }

    /**
     * changepassword
     *
     * @return array
     */
    final public function changepassword()
    {
        $postedDatas = $this->getParams();
        $form = new passwordForm($postedDatas);
        if ($this->isPost()) {
            $uid = $postedDatas[self::_ID];
            $userDatas = (array) $this->userModel->getById($uid);
            $newPassword = $postedDatas['newpassword1'];
            $doubleCheckPassword = ($newPassword == $postedDatas['newpassword2']);
            $passwordCheck = ($userDatas['password'] == $this->getParams('oldpassword'));
            if ($form->isValid() && $doubleCheckPassword && $passwordCheck) {
                $userData = $this->userModel->getById($uid);
                $this->userModel->setWhere([self::_ID => $uid]);
                $this->userModel->bindWhere();
                $updateData = array(
                    'password' => $newPassword
                    , 'token' => \Pimvc\Tools\User\Token::get(
                        $postedDatas[self::_EMAIL],
                        $postedDatas['password']
                    )
                );
                unset($userData);
                $this->userModel->update($updateData);
                unset($userDatas);
                $message = 'Le mot de passe a correctement été changé.';
            } else {
                $DCMessage = (!$doubleCheckPassword) ? '<p style="color:red">Saisie nouveaux mots de passe inconrrecte.</p>' : '';
                $PCMessage = (!$passwordCheck) ? '<p style="color:red">L\'ancien mot de passe ne correspond pas.</p>' : '';
                $message = $DCMessage . $PCMessage . (string) $form;
                unset($userDatas);
            }
        } else {
            $message = (string) $form;
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::LOCK)
            . 'Changer mon mot de passe',
            (string) $form
        );
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * lostpassword
     *
     * @return Response
     */
    final public function lostpassword()
    {
        $formData = $this->getParams();
        $form = new lostPasswordForm($formData);
        $content = '';
        if ($this->isPost()) {
            $mailSent = false;
            $mailError = '';
            if ($form->isValid()) {
                $user = $this->userModel->getByEmail($formData[self::_EMAIL]);
                if ($user) {
                    $tplPath = $this->getApp()->getPath() . 'Views/User/Mail/Lostpassword.php';
                    $mailBody = (new \Pimvc\View())
                        ->setFilename($tplPath)
                        ->setParams(['user' => $user])
                        ->render();
                    $mailSender = new mailSender();
                    $mailSender->setFrom('pf@pier-infor.fr')
                        ->setTo($user->email)
                        ->setSubject('Password retrieval')
                        ->setBody($mailBody);
                    try {
                        $mailSender->send();
                        $mailSent = true;
                    } catch (\Exception $ex) {
                        $mailError = $ex->getMessage();
                    }
                }
                $messageType = ($user && $mailSent) ? flashTools::FLASH_INFO : flashTools::FLASH_ERROR;
                $message = ($user) ? self::MAIL_MESSAGE_NOTIFY_COMPLETE : self::MAIL_MESSAGE_NOTIFY_NOUSER;
                $message = ($mailSent) ? $message : $mailError;
                flashTools::add($messageType, $message);
                $content = (string) $form;
            } else {
                flashTools::addError('Email invalide');
                $content = (string) $form;
            }
        } else {
            $content = (string) $form;
        }
        unset($form);
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::LOCK)
            . 'Mot de passe perdu' . $this->getLostPasswordLinks(),
            $content
        );
        return (string) $this->getLayout((string) $widget);
    }
}
