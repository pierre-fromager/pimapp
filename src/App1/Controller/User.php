<?php
/**
 * Description of App1\Controller\User
 *
 * @author Pierre Fromager
 */
namespace App1\Controller;

use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Input\Custom\Filters\Range as inputRange;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Flash as flashTools;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\User\Auth as authTools;
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use App1\Form\Users\Search as searchUsersForm;
use App1\Form\Users\Edit as editUsersForm;
use App1\Form\Users\Password as passwordForm;
use App1\Views\Helpers\Form\Search\Filter as formFilter;
use App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
use App1\Form\Users\Login as loginForm;
use App1\Form\Users\Register as registerForm;

final class User extends basicController
{
    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';
    const PARAM_ID = 'id';
    const PARAM_ORDER = 'order';
    const PARAM_NAME = 'name';
    const PARAM_STATUS = 'status';
    const PARAM_PROFIL = 'profil';
    const PARAM_TOKEN = 'token';
    const PARAM_EMAIL = 'email';
    const PARAM_LOGIN = 'login';
    const PARAM_PASSWORD = 'password';
    const PARAM_TITLE = 'title';
    const VIEW_USER_PATH = '/Views/User/';
    const WILDCARD = '%';
    const PHP_EXT = '.php';
    const LAYOUT_NAME = 'responsive';
    const USER_MESSAGE_DISCONECTED = 'Vous êtes déconnecté.';
    const PARAM_PAGESIZE = 'pagesize';
    const ERP_ASSIST_USER = 'user';
    const PARAM_RESET = 'reset';
    const AJAX_TERM = 'term';
    const LIST_ACTION = 'user/manage';
    const DETAIL_ACTION = 'user/detail';
    const LIST_MODEL = 'Users';
    const USER_MESSAGE_VALDATED = 'Mise à jour effectuée.';
    const USER_MESSAGE_REGISTRATION_SUCCESS = 'Enregistrement réussi.';
    const USER_MESSAGE_REGISTRATION_FAILED = 'Adresse email déjà utilisée.';
    const USER_MESSAGE_REGISTRATION_INVALID = 'Les champs obligatoires n\'ont pas été saisis correctement.';
    const FORM_INCOMPLETE_MESSAGE = 'Formulaire incomplet.';
    const USER_MESSAGE_DELETE_SUCCESS = 'Enregistrement supprimé';

    private $modelConfig;
    private $userModel;
    private $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getApp()->getConfig()->getSettings('dbPool');
        $this->userModel = new \App1\Model\Users($this->modelConfig);
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->setAssets();
    }

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
        $links = '<div style="float:right">'
            . glyphHelper::getLinked(
                glyphHelper::CERTIFICATE,
                $this->baseUrl . DIRECTORY_SEPARATOR . 'user/register',
                [self::PARAM_TITLE => 'Register']
            )
            . '</div>';
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
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    private function getLayout($content)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => $content];
        $layout->setApp($this->getApp())
            ->setName(self::LAYOUT_NAME)
            ->setLayoutParams($layoutParams)
            ->build();
        return $layout;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    private function getNavConfig()
    {
        $items = [];
        $isAuth = sessionTools::isAuth();
        $isPro = sessionTools::getProfil() === 'pro';
        $authLink = ($isAuth) ? [
            self::PARAM_TITLE => 'Logout'
            , 'icon' => 'fa fa-sign-out'
            , 'link' => $this->baseUrl . '/user/logout'
            ] : [
            self::PARAM_TITLE => 'Login'
            , 'icon' => 'fa fa-sign-in'
            , 'link' => $this->baseUrl . '/user/login'
            ];

        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $items += [
                [
                    self::PARAM_TITLE => 'Acl'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/acl/manage'
                ],
                [
                    self::PARAM_TITLE => 'Password'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/user/changepassword'
                ],
                [
                    self::PARAM_TITLE => 'Database'
                    , 'icon' => 'fa fa-database'
                    , 'link' => $this->baseUrl . '/database/tablesmysql'
                ],
            ];
        }

        if ($isAuth) {
            $authItems = [];
            $items = array_merge($items, $authItems);
        }

        array_push($items, $authLink);
        $navConfig = [
            self::PARAM_TITLE => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => $items
        ];
        return $navConfig;
    }

    /**
     * setPageSize
     *
     */
    private function setPageSize()
    {
        if ($this->getParams(self::PARAM_PAGESIZE)) {
            sessionTools::set(
                self::PARAM_PAGESIZE,
                $this->getParams(self::PARAM_PAGESIZE)
            );
        }
    }

    /**
     * getAssist
     *
     * @return array
     */
    private function getAssist()
    {
        return sessionAssistTools::getSearch(
            self::ERP_ASSIST_USER,
            $this->getApp()->getRequest(),
            $this->getParams(self::PARAM_RESET)
        );
    }

    /**
     * checkIntegrity
     *
     *  security enforcement to avoid owner usurpation and profil escalation
     *
     * @param array $params
     */
    private function checkIntegrity($params)
    {
        $integrity = true;
        if (!sessionTools::isAdmin()) {
            $integrityOwner = sessionTools::isMine($params[self::PARAM_ID]);
            $integrityProfil = (isset($params[self::PARAM_PROFIL])) ? sessionTools::getProfil() == $params[self::PARAM_PROFIL] : true;
            $integrity = ($integrityOwner && $integrityProfil);
        }
        return $integrity;
    }

    /**
     * setAssets
     *
     */
    private function setAssets()
    {
        $cssAssets = [
            'widget.css', 'tables/table-6.css', 'jquery.selectbox.css',
            'chosen.css', 'form_responsive.css', 'main.css'
        ];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add(self::PUBLIC_CSS . $cssAssets[$c]);
        }
        cssCollection::save();
        $jsAssets = [
            'sortable.js', 'chosen.jquery.js', 'jquery.autogrow.js',
            'jquery.columnmanager.js', 'jquery.cookie.js'
        ];
        for ($c = 0; $c < count($jsAssets); $c++) {
            jsCollection::add(self::PUBLIC_JS . $jsAssets[$c]);
        }
        jsCollection::save();
    }

    /**
     * createUser
     *
     * @return boolean
     */
    private function createUser()
    {
        $params = $this->getParams();
        $params[self::PARAM_PROFIL] = 'user';
        $params[self::PARAM_STATUS] = 'waiting';
        $params[self::PARAM_EMAIL] = strtolower($params[self::PARAM_LOGIN]);
        $params[self::PARAM_LOGIN] = strtolower($params[self::PARAM_LOGIN]);
        $params['datec'] = \date('Y-m-d H:i:s');
        $domainUser = $this->userModel->getDomainInstance();
        $domainUser->hydrate($params);
        $this->userModel->save($domainUser);
        $hasError = $this->userModel->hasError();
        $lastInsert = $this->userModel->getLastInsertId();
        $offer = 'demo';
        $this->userModel->setExpirationDate($lastInsert, $offer);
        return $hasError;
    }

    /**
     * getIndexInputFilter
     *
     * @return inputFilter
     */
    private function getIndexInputFilter()
    {
        return new inputFilter(
            $this->getParams(),
            [
            self::PARAM_ID => new inputRange([
                inputRange::MIN_RANGE => 1,
                inputRange::MAX_RANGE => 10000,
                inputRange::_DEFAULT => 800,
                inputRange::CAST => inputRange::FILTER_INTEGER
                ]),
            self::PARAM_EMAIL => FILTER_SANITIZE_STRING
            ]
        );
    }

    /**
     * getLoginInputFilter
     *
     * @return inputFilter
     */
    private function getLoginInputFilter($postedDatas)
    {
        return new inputFilter(
            $postedDatas,
            [
            self::PARAM_LOGIN => FILTER_SANITIZE_EMAIL,
            self::PARAM_PASSWORD => FILTER_SANITIZE_STRING,
            self::PARAM_TOKEN => FILTER_SANITIZE_STRING
            ]
        );
    }
}
