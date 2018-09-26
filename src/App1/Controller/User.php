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
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use App1\Form\Users\Search as searchUsersForm;
use App1\Form\Users\Edit as editUsersForm;
use App1\Form\Users\Password as passwordForm;
use App1\Form\Users\Lostpassword as lostPasswordForm;
use App1\Form\Users\Login as loginForm;
use App1\Form\Users\Register as registerForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
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
            [self::PARAM_ID, self::PARAM_EMAIL],
            [
                self::PARAM_ID . '#>' => (isset($input->id)) ? $input->id : 800
                , self::PARAM_EMAIL => (isset($input->email)) ? self::WILDCARD . $input->email . self::WILDCARD : self::WILDCARD
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
                return $this->redirect($this->baseUrl . DIRECTORY_SEPARATOR . $authAction);
            } else {
                flashTools::addError('Authentication failed');
                return $this->redirect($this->baseUrl . '/home');
            }
        }
        $viewParams = ['form' => (string) $form];
        $view = $this->getView(
            $viewParams,
            self::VIEW_USER_PATH . ucfirst(__FUNCTION__) . self::PHP_EXT
        );
        $registerLink = glyphHelper::getLinked(
            glyphHelper::CERTIFICATE,
            $this->baseUrl . '/user/register',
            [self::PARAM_TITLE => 'Register']
        );
        $lostpasswdLink = glyphHelper::getLinked(
            glyphHelper::LOCK,
            $this->baseUrl . '/user/lostpassword',
            [self::PARAM_TITLE => 'Lost password']
        );
        $links = '<div style="float:right">' . $registerLink . $lostpasswdLink . '</div>';
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        $widgetTitle = '<span class="fa fa-sign-in"></span>Login' . $links;
        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody((string) $view);
        $widget->render();
        return (string) $this->getLayout((string) $nav . (string) $widget);
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
                $userExists = $this->userModel->userExists($this->getParams(self::PARAM_LOGIN));
                if (!$userExists) {
                    $hasError = $this->createUser();
                    $messageType = ($hasError) ? 'error' : 'info';
                    $message = ($hasError) ? $this->userModel->getError() : self::USER_MESSAGE_REGISTRATION_SUCCESS;
                    flashTools::add($messageType, $message);
                    $redirectAction = ($hasError) ? 'user/register/type/' . $this->getParams(self::PARAM_PROFIL) : 'user/login';
                    $redirectUrl = $this->baseUrl . DIRECTORY_SEPARATOR . $redirectAction;
                    return $this->redirect($redirectUrl);
                } else {
                    flashTools::addError(self::USER_MESSAGE_REGISTRATION_FAILED);
                }
            } else {
                $errorMessages = $form->getErrors();
                foreach ($form->getErrors() as $key => $value) {
                    flashTools::addWarning($key . ' : ' . $value);
                }
            }
        }
        $widget = (new widgetHelper())->setTitle('Register')->setBody((string) $form);
        unset($form);
        $widget->render();
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $widget);
    }

    /**
     * manage
     *
     * return Response
     */
    final public function manage()
    {
        $hasContext = $this->hasValue('context');
        $this->setPageSize();
        $criterias = $this->getAssist();
        $form = new searchUsersForm($criterias);
        $form->setEnableResetButton(true);
        $form->render();
        $filter = formFilter::get((string) $form);
        unset($form);
        $excludeToolbarAction = array(
            glyphToolbar::EXCLUDE_DETAIL => false
            , glyphToolbar::EXCLUDE_IMPORT => true
            , glyphToolbar::EXCLUDE_NEWSLETTER => true
            , glyphToolbar::EXCLUDE_PDF => true
            , glyphToolbar::EXCLUDE_CLONE => false
            , glyphToolbar::EXCLUDE_PEOPLE => true
            , glyphToolbar::EXCLUDE_REFUSE => true
        );
        $listeFields = array(self::PARAM_ID, self::PARAM_NAME, self::PARAM_LOGIN, self::PARAM_STATUS);
        $listeExclude = array_diff(
            $this->userModel->getDomainInstance()->getVars(),
            $listeFields
        );
        $liste = new \Pimvc\Liste(
            get_class($this->userModel),
            self::LIST_ACTION,
            $listeExclude,
            $excludeToolbarAction,
            $this->getParams('page'),
            $criterias,
            array(),
            array(self::PARAM_ORDER => 'desc')
        );
        $whereConditions = array('key' => self::PARAM_STATUS, 'value' => 'valid');
        $conditions = array(
            glyphToolbar::EXCLUDE_VALIDATE => $whereConditions
        );
        $liste->setActionCondition($conditions);
        $liste->setLabels(editUsersForm::_getStaticLabels($withIcons = false));
        $liste->setFormater(self::PARAM_PROFIL, 'Helper_Format_Roles::getFliped');
        if ($hasContext) {
            $this->getJsonHeaders();
            echo $liste->getJson();
            die;
        }
        $liste->render();
        $widgetTitle = glyphHelper::get(glyphHelper::SEARCH)
            . 'Gestion des comptes utilisateurs'
            . '<div style="float:right">'
            /*
              . glyphHelper::getLinked(
              glyphHelper::book
              , $this->baseUrl . 'log/sys'
              , [self::PARAM_TITLE => 'Journaux système']
              ) . glyphHelper::getLinked(
              glyphHelper::GLOBE
              , $this->baseUrl . 'log/ipcountry/date/' . date('Y-m')
              , [self::PARAM_TITLE => 'Country Hits']
              ) . glyphHelper::getLinked(
              glyphHelper::LEAF
              , $this->baseUrl . 'Social_Twitter/auth'
              , [self::PARAM_TITLE => 'Twitter Auth']
              ) */
            . '</div>';
        $widget = (new widgetHelper())
            ->setTitle($widgetTitle)
            ->setBody(
                $filter
                . '<div class="table-responsive">' . (string) $liste . '</div>'
            );
        unset($liste);
        $widget->render();
        $content = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $content);
    }

    /**
     * duplicate
     *
     * return Response
     */
    final public function duplicate()
    {
        $id = $this->getParams(self::PARAM_ID);
        if ($id) {
            $userObject = $this->userModel->getById($id);
            unset($userObject->id);
            $this->userModel->save($userObject);
            flashTools::addInfo('User id ' . $id . ' dupliqué.');
            $redirectUrl = $this->baseUrl . DIRECTORY_SEPARATOR . self::LIST_ACTION;
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
        $uid = ($this->hasValue(self::PARAM_ID)) ? $this->getParams(self::PARAM_ID) : sessionTools::getUid();
        $this->userModel->cleanRowset();
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
        $isAdmin = sessionTools::isAdmin();
        $postedDatas = ($isPost) ? $this->getParams() : (array) $this->userModel->getById($uid);
        if (isset($postedDatas[self::PARAM_EMAIL])) {
            $postedDatas[self::PARAM_EMAIL] = strtolower($postedDatas[self::PARAM_EMAIL]);
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
                $postedDatas[self::PARAM_TOKEN] = \Pimvc\Tools\User\Token::get(
                    $postedDatas[self::PARAM_EMAIL],
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
                    return $this->redirect($this->baseUrl . DIRECTORY_SEPARATOR . $redirectÀction);
                }
            } else {
                foreach ($form->getErrors() as $k => $v) {
                    flashTools::addError($v);
                }
                $message = (string) $form;
            }
        } else {
            $message = (string) $form;
        }
        $linkDetailId = ($this->hasValue(self::PARAM_ID)) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        $linkManage = ($isAdmin) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . DIRECTORY_SEPARATOR . 'user/manage',
            [self::PARAM_TITLE => 'Comptes']
        ) : '';
        $linkDetail = glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->baseUrl . 'user/detail' . DIRECTORY_SEPARATOR . $linkDetailId,
            [self::PARAM_TITLE => 'Détail']
        );
        $linkIntervenant = glyphHelper::getLinked(
            glyphHelper::FOLDER_OPEN,
            $this->baseUrl . DIRECTORY_SEPARATOR . 'intervenant/edit/uid/' . $uid,
            [self::PARAM_TITLE => 'Edition']
        );
        $links = '<div style="float:right">'
            . $linkManage
            . $linkDetail
            . $linkIntervenant
            . '</div>';
        $widgetTitle = glyphHelper::get(glyphHelper::PENCIL)
            . 'Edition du compte' . $links;
        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody((string) $message);
        $widget->render();
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . (string) $widget);
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
        $uid = ($this->hasValue(self::PARAM_ID)) ? $this->getParams(self::PARAM_ID) : sessionTools::getUid();
        $this->userModel->cleanRowset();
        $formDatas = (array) $this->userModel->getById($uid);
        $form = new \App1\Form\Users\Edit(
            $formDatas,
            $uid,
            $mode = 'readonly'
        );
        $form->setEnableButtons(false);
        $form->render();
        $linkEditId = ($this->hasValue(self::PARAM_ID)) ? '/id/' . $this->getParams(self::PARAM_ID) : '';
        $manageButton = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . DIRECTORY_SEPARATOR . 'user/manage' . $linkEditId,
            array(self::PARAM_TITLE => 'Gestion des comptes')
        ) : '';
        $links = '<div style="float:right">'
            . glyphHelper::getLinked(
                glyphHelper::PENCIL,
                $this->baseUrl . DIRECTORY_SEPARATOR . 'user/edit' . $linkEditId,
                array(self::PARAM_TITLE => 'Edition du compte')
            ) . $manageButton
            . '</div>';
        $widgetTitle = glyphHelper::get(glyphHelper::EYE_OPEN)
            . 'Détail du compte' . $links;
        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody((string) $form);
        $widget->render();
        $detailContent = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . $detailContent);
    }

    /**
     * deleteAction
     *
     * @return array
     */
    final public function delete()
    {
        if ($this->hasValue(self::PARAM_ID)) {
            $this->userModel->cleanRowset();
            $where = [self::PARAM_ID => $this->getParams(self::PARAM_ID)];
            $this->userModel->setWhere($where);
            $this->userModel->bindWhere();
            $this->userModel->delete();
            $hasError = $this->userModel->hasError();
            $messageType = ($hasError) ? 'error' : 'info';
            $message = ($hasError) ? self::USER_MESSAGE_DELETE_ERROR . $this->userModel->getError() : self::USER_MESSAGE_DELETE_SUCCESS;
            flashTools::add($messageType, $message);
            return $this->redirect($this->baseUrl . DIRECTORY_SEPARATOR . self::LIST_ACTION);
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
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
        if ($isPost) {
            $uid = $postedDatas['id'];
            $userDatas = (array) $this->userModel->getById($uid);
            $newPassword = $postedDatas['newpassword1'];
            $doubleCheckPassword = ($newPassword == $postedDatas['newpassword2']);
            $passwordCheck = ($userDatas['password'] == $this->getParams('oldpassword'));
            if ($form->isValid() && $doubleCheckPassword && $passwordCheck) {
                $userData = $this->userModel->getById($uid);
                $this->userModel->setWhere(array('id' => $uid));
                $this->userModel->bindWhere();
                $updateData = array(
                    'password' => $newPassword
                    , 'token' => \Pimvc\Tools\User\Token::get(
                        $postedDatas[self::PARAM_EMAIL],
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

        $widgetTitle = glyphHelper::get(glyphHelper::LOCK)
            . 'Changer mon mot de passe';

        $widget = (new widgetHelper())
            ->setTitle($widgetTitle)
            ->setBody((string) $form);
        $widget->render();
        $detailContent = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . $detailContent);
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
        $isPost = ($this->getApp()->getRequest()->getMethod() === 'POST');
        $content = '';
        if ($isPost) {
            $mailSent = false;
            $mailError = '';
            if ($form->isValid()) {
                $user = $this->userModel->getByEmail($formData['email']);
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
        $loginLink = glyphHelper::getLinked(
            glyphHelper::LOG_IN,
            $this->baseUrl . '/user/login',
            ['title' => 'Se connecter']
        );
        $registerLink = glyphHelper::getLinked(
            glyphHelper::CERTIFICATE,
            $this->baseUrl . '/user/register',
            ['title' => 'Enregistrement']
        );
        $links = '<div style="float:right">' . $loginLink . $registerLink . '</div>';
        $widgetTitle = glyphHelper::get(glyphHelper::LOCK)
            . 'Mot de passe perdu' . $links;

        $widget = (new widgetHelper())->setTitle($widgetTitle)->setBody($content);
        $widget->render();
        $detailContent = (string) $widget;
        unset($widget);
        $nav = (new bootstrapNav());
        $nav->setParams($this->getNavConfig())->render();
        return (string) $this->getLayout((string) $nav . $detailContent);
    }
}
