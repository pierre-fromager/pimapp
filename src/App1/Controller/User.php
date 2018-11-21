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
use \Pimvc\Views\Helpers\Fa as faHelper;
use \Pimvc\Tools\User\Auth as authTools;
use \App1\Form\Users\Search as searchUsersForm;
use \App1\Form\Users\Edit as editUsersForm;
use \App1\Form\Users\Password as passwordForm;
use \App1\Form\Users\Lostpassword as lostPasswordForm;
use \App1\Form\Users\Login as loginForm;
use \App1\Form\Users\Register as registerForm;
use \App1\Views\Helpers\Form\Search\Filter as formFilter;
use \App1\Helper\Controller\User as helperUserController;
use \App1\Helper\Lang\IEntries as ILang;

final class User extends helperUserController
{

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
                $authAction = ($auth->profil === 'admin') ? '/user/manage' : '/user/edit';
                return $this->redirect($this->baseUrl . $authAction);
            } else {
                flashTools::addError($this->translate(ILang::__AUTH_FAIL));
            }
        } else {
            if ($this->isPost()) {
                flashTools::addError($this->translate(ILang::__INVALID_CREDENTIAL));
            }
        }
        $view = $this->getView(
            ['form' => (string) $form],
            self::VIEW_USER_PATH . ucfirst(__FUNCTION__) . self::PHP_EXT
        );
        unset($form);
        $widget = $this->getWidget(
            faHelper::get(faHelper::SIGN_IN)
            . $this->translate(ILang::__LOGIN_LABEL)
            . $this->getLoginLinks(),
            (string) $view
        );
        return $this->getHtmlResponse(
            $this->getLayout((string) $widget),
            'lastlogin',
            (new \DateTime())->format(('Y-m-d\TH:i:s.u'))
        );
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
        if ($this->isPost()) {
            if ($form->isValid()) {
                $userExists = $this->userModel->userExists($this->getParams(self::_LOGIN));
                if (!$userExists) {
                    $hasError = $this->createUser();
                    $messageType = ($hasError) ? flashTools::FLASH_ERROR : flashTools::FLASH_SUCCESS;
                    $message = ($hasError) ? $this->userModel->getError() : $this->translate(ILang::__USER_MESSAGE_REGISTRATION_SUCCESS);
                    flashTools::add($messageType, $message);
                    $redirectAction = ($hasError) ? '/user/register/type/' . $this->getParams(self::_PROFIL) : '/user/login';
                    $redirectUrl = $this->baseUrl . $redirectAction;
                    return $this->redirect($redirectUrl);
                } else {
                    flashTools::addError(
                        $this->translate(ILang::__USER_MESSAGE_REGISTRATION_FAIL)
                    );
                }
            } else {
                $errorMessages = $form->getErrors();
                foreach ($errorMessages as $key => $value) {
                    flashTools::addWarning($key . ' : ' . $value);
                }
            }
        }
        $widget = $this->getWidget(
            $this->translate(ILang::__USERS_SIGN_UP),
            (string) $form
        );
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
        $criterias = $this->getAssist(helperUserController::ERP_ASSIST_USER);
        $form = new searchUsersForm($criterias);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get(
            (string) $form,
            [self::_TITLE => $this->translate(ILang::__COLUMNS)]
        );
        unset($form);
        $liste = $this->getManageList($criterias);
        if ($this->hasValue('context')) {
            return $this->getJsonResponse($liste->getJson());
        }
        $widgetTitle = glyphHelper::get(glyphHelper::SEARCH)
            . $this->translate(ILang::__USER_ACOUNT_MANAGEMENT);
        $widget = $this->getWidget(
            $widgetTitle,
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
                        $message = $this->translate(ILang::__USER_EDIT_INTEG_ERROR);
                        flashTools::addError($message);
                        return $this->redirect($this->baseUrl . '/user/detail');
                    }
                }
                if (isset($postedDatas[\Pimvc\Form::FORM_XCSRF])) {
                    unset($postedDatas[\Pimvc\Form::FORM_XCSRF]);
                }
                $postedDatas[self::_TOKEN] = \Pimvc\Tools\User\Token::get(
                    $postedDatas[self::_EMAIL],
                    $postedDatas[self::_PASSWORD]
                );
                $postedDatas['ip'] = $this->getApp()->getRequest()->getRemoteAddr();
                $domainInstance = $this->userModel->getDomainInstance();
                $domainInstance->hydrate($postedDatas);
                $this->userModel->saveDiff($domainInstance);
                unset($domainInstance);
                $hasError = $this->userModel->hasError();
                if ($hasError) {
                    $message = self::USER_MESSAGE_ERROR . $this->_model->getError();
                    flashTools::addError($message);
                } else {
                    $redirectÀction = ($isAdmin) ? self::LIST_ACTION : self::DETAIL_ACTION;
                    flashTools::addSuccess(self::USER_MESSAGE_VALDATED);
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
            . $this->translate(ILang::__USERS_EDIT_TITLE) . $this->getEditLinks($uid),
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
        $uid = ($this->hasValue(self::_ID)) ? $this->getParams(self::_ID) : sessionTools::getUid();
        $this->userModel->cleanRowset();
        $formDatas = (array) $this->userModel->getById($uid);
        $form = new editUsersForm($formDatas, $uid, 'readonly');
        $form->setEnableButtons(false);
        $form->render();
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::EYE_OPEN)
            . $this->translate(ILang::__USER_DETAIL_TITLE) . $this->getDetailLinks(),
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
            $messageType = ($hasError) ? flashTools::FLASH_ERROR : flashTools::FLASH_SUCCESS;
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
            $passwordCheck = ($userDatas[self::_PASSWORD] == $this->getParams('oldpassword'));
            if ($form->isValid() && $doubleCheckPassword && $passwordCheck) {
                $userData = $this->userModel->getById($uid);
                $this->userModel->setWhere([self::_ID => $uid]);
                $this->userModel->bindWhere();
                $updateData = array(
                    self::_PASSWORD => $newPassword
                    , self::_TOKEN => \Pimvc\Tools\User\Token::get(
                        $postedDatas[self::_EMAIL],
                        $postedDatas[self::_PASSWORD]
                    )
                );
                unset($userData);
                $this->userModel->update($updateData);
                unset($userDatas);
                flashTools::addSuccess($this->translate(ILang::__USER_CHANGE_PASSWORD_SUCCESS));
            } else {
                if (!$doubleCheckPassword || !$passwordCheck) {
                    flashTools::addError($this->translate(ILang::__INVALID_CREDENTIAL));
                }
                unset($userDatas);
            }
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::LOCK) . $this->translate(ILang::__CHANGE_PASSWORD),
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
                    $template = $this->getApp()->getPath() . 'Views/User/Mail/Lostpassword.php';
                    $mailBody = (new \Pimvc\View())
                        ->setFilename($template)
                        ->setParams(['user' => $user])
                        ->render();
                    $mailError = $this->sendMail(
                        $this->getConfigSettings('app')['mailer']['from'],
                        $user->email,
                        $this->translate(ILang::__LOST_PASSWORD),
                        $mailBody
                    );
                    $mailSent = strlen($mailError) === 0;
                }
                $messageType = ($user && $mailSent) ? flashTools::FLASH_SUCCESS : flashTools::FLASH_ERROR;
                $message = ($user) ? ILang::__MAIL_SENT_SUCCESS : ILang::__MAIL_SENT_ERROR;
                $message = ($mailSent) ? $message : $mailError;
                flashTools::add($messageType, $this->translate($message));
                $content = (string) $form;
            } else {
                flashTools::addError($this->translate(ILang::__INCORRECT_EMAIL));
            }
        }
        $widget = $this->getWidget(
            glyphHelper::get(glyphHelper::LOCK)
            . $this->translate(ILang::__LOST_PASSWORD)
            . $this->getLostPasswordLinks(),
            (string) $form
        );
        unset($form);
        return (string) $this->getLayout((string) $widget);
    }

    /**
     * validate
     *
     * @return Response
     */
    final public function validate()
    {
        if ($uid = $this->getParams(self::_ID)) {
            $this->userModel->validate($uid);
            $user = $this->userModel->getById($uid);
            $template = $this->getApp()->getPath() . 'Views/User/Mail/Validate.php';
            $mailBody = (new \Pimvc\View())
                ->setFilename($template)
                ->setParams(['user' => $user])
                ->render();
            $mailError = $this->sendMail(
                $this->getConfigSettings('app')['mailer']['from'],
                $user->email,
                $this->translate(ILang::__USER_ACOUNT_ENABLE),
                $mailBody
            );
            $mailSent = strlen($mailError) === 0;
            $messageType = ($mailSent) ? flashTools::FLASH_SUCCESS : flashTools::FLASH_ERROR;
            $message = ($mailSent) ? $this->translate(ILang::__MAIL_SENT_SUCCESS) : $this->translate(ILang::__MAIL_SENT_ERROR);
            flashTools::add($messageType, $message);
        }
        $redirectUrl = $this->baseUrl . '/user/manage';
        return $this->redirect($redirectUrl);
    }
}
