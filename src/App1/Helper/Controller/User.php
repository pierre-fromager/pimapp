<?php
/**
 * Description of App1\Helper\Controller\User
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Views\Helpers\Fa as faHelper;
use \App1\Model\Users as modelUser;
use \App1\Form\Users\Edit as editUsersForm;
use \App1\Helper\Lang\IEntries as ILang;

class User extends basicController
{

    use \App1\Helper\Reuse\Controller;

    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';
    const _ID = modelUser::_ID;
    const _EMAIL = modelUser::_EMAIL;
    const _PASSWORD = modelUser::_PASSWORD;
    const _PROFIL = modelUser::_PROFIL;
    const _STATUS = modelUser::_STATUS;
    const _TOKEN = modelUser::_TOKEN;
    const _LOGIN = modelUser::_LOGIN;
    const _NAME = 'name';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _TEXT = 'text';
    const _ITEMS = 'items';
    const _ORDER = 'order';
    const _PAGE = 'page';
    const VIEW_USER_PATH = '/Views/User/';
    const WILDCARD = '%';
    const PHP_EXT = '.php';
    const LAYOUT_NAME = 'responsive';
    const USER_MESSAGE_DISCONECTED = 'Vous êtes déconnecté.';
    const _PAGESIZE = 'pagesize';
    const ERP_ASSIST_USER = 'user';
    const PARAM_RESET = 'reset';
    const AJAX_TERM = 'term';
    const LIST_ACTION = 'user/manage';
    const DETAIL_ACTION = 'user/detail';
    const LIST_MODEL = 'Users';
    const USER_MESSAGE_VALDATED = 'Mise à jour effectuée.';
    const FORM_INCOMPLETE_MESSAGE = 'Formulaire incomplet.';
    const USER_MESSAGE_DELETE_SUCCESS = 'Enregistrement supprimé';
    const MAIL_MESSAGE_NOTIFY_COMPLETE = 'Un email vous a été adressé contenant les informations nécessaires.';
    const MAIL_MESSAGE_NOTIFY_NOUSER = 'Vous n\'êtes pas inscrit.';

    protected $modelConfig;
    protected $userModel;
    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getConfigSettings('dbPool');
        $this->userModel = new modelUser($this->modelConfig);
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->setAssets();
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig()
    {
        $items = [];
        $isAuth = sessionTools::isAuth();
        $authLink = $this->menuAction(
            ($isAuth) ? $this->translate(ILang::__LOGOUT) : $this->translate(ILang::__LOGIN),
            ($isAuth) ? faHelper::getFontClass(faHelper::SIGN_OUT) : faHelper::getFontClass(faHelper::SIGN_IN),
            ($isAuth) ? '/user/logout' : '/user/login'
        );
        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $items += [
                $this->menuAction(
                    $this->translate(ILang::__PERMISSIONS),
                    faHelper::getFontClass(faHelper::LOCK),
                    '/acl/manage'
                ),
                $this->menuAction(
                    $this->translate(ILang::__CHANGE_PASSWORD),
                    faHelper::getFontClass(faHelper::LOCK),
                    '/user/changepassword'
                ),
                $this->menuAction(
                    $this->translate(ILang::__DATABASE),
                    faHelper::getFontClass(faHelper::DATABASE),
                    '/database/tablesmysql'
                )
            ];
        }
        if ($isAuth) {
            $authItems = [];
            $items = array_merge($items, $authItems);
        }
        array_push($items, $authLink);
        $navConfig = [
            self::_TITLE => [
                self::_TEXT => $this->translate(ILang::__HOME),
                self::_ICON => faHelper::getFontClass(faHelper::HOME),
                self::_LINK => $this->baseUrl
            ],
            self::_ITEMS => $items
        ];
        return $navConfig;
    }

    /**
     * getManageList
     *
     * @param array $criterias
     * @return \Pimvc\Liste
     */
    protected function getManageList(array $criterias)
    {
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
            $this->getParams(self::_PAGE),
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
        $liste->render();
        return $liste;
    }

    /**
     * getEditLinks
     *
     * @param int $uid
     * @return string
     */
    protected function getEditLinks(int $uid): string
    {
        $linkDetailId = ($this->hasValue(self::_ID)) ? '/id/' . $this->getParams(self::_ID) : '';
        $manage = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . '/user/manage',
            [self::_TITLE => $this->translate(ILang::__USER_ACOUNT_MANAGEMENT)]
        ) : '';
        $detail = glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->baseUrl . '/user/detail/' . $linkDetailId,
            [self::_TITLE => 'Détail']
        );
        /*
          $intervenant = glyphHelper::getLinked(
          glyphHelper::FOLDER_OPEN,
          $this->baseUrl . '/intervenant/edit/uid/' . $uid,
          [self::_TITLE => 'Edition']
          ); */
        $password = glyphHelper::getLinked(
            glyphHelper::LOCK,
            $this->baseUrl . '/user/changepassword',
            [self::_TITLE => $this->translate(ILang::__CHANGE_PASSWORD)]
        );
        $links = $manage . $detail . $password;
        return $this->getWidgetLinkWrapper($links);
    }

    /**
     * getLoginLinks
     *
     * @return string
     */
    protected function getLoginLinks(): string
    {
        $registerLink = glyphHelper::getLinked(
            glyphHelper::CERTIFICATE,
            $this->baseUrl . '/user/register',
            [self::_TITLE => $this->translate(ILang::__USERS_SIGN_UP)]
        );
        $lostpasswdLink = glyphHelper::getLinked(
            glyphHelper::LOCK,
            $this->baseUrl . '/user/lostpassword',
            [self::_TITLE => $this->translate(ILang::__LOST_PASSWORD)]
        );
        return $this->getWidgetLinkWrapper($registerLink . $lostpasswdLink);
    }

    /**
     * getDetailLinks
     *
     * @return string
     */
    protected function getDetailLinks(): string
    {
        $linkEditId = ($this->hasValue(self::_ID)) ? '/id/' . $this->getParams(self::_ID) : '';
        $manageButton = (sessionTools::isAdmin()) ? glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->baseUrl . '/user/manage' . $linkEditId,
            [self::_TITLE => $this->translate(ILang::__USER_ACOUNT_MANAGEMENT)]
        ) : '';

        $editLink = glyphHelper::getLinked(
            glyphHelper::PENCIL,
            $this->baseUrl . '/user/edit' . $linkEditId,
            [self::_TITLE => $this->translate(ILang::__USERS_EDIT_TITLE)]
        );
        return $this->getWidgetLinkWrapper($manageButton . $editLink);
    }

    /**
     * getLostPasswordLinks
     *
     * @return string
     */
    protected function getLostPasswordLinks(): string
    {

        $loginLink = glyphHelper::getLinked(
            glyphHelper::LOG_IN,
            $this->baseUrl . '/user/login',
            [self::_TITLE => $this->translate(ILang::__USERS_SIGN_IN)]
        );
        $registerLink = glyphHelper::getLinked(
            glyphHelper::CERTIFICATE,
            $this->baseUrl . '/user/register',
            [self::_TITLE => $this->translate(ILang::__USERS_SIGN_UP)]
        );
        return $this->getWidgetLinkWrapper($loginLink . $registerLink);
    }

    /**
     * checkIntegrity
     *
     *  security enforcement to avoid owner usurpation and profil escalation
     *
     * @param array $params
     */
    protected function checkIntegrity($params)
    {
        $integrity = true;
        if (!sessionTools::isAdmin()) {
            $integrityOwner = sessionTools::isMine($params[self::_ID]);
            $integrityProfil = (isset($params[self::_PROFIL])) ? sessionTools::getProfil() == $params[self::_PROFIL] : true;
            $integrity = ($integrityOwner && $integrityProfil);
        }
        return $integrity;
    }

    /**
     * setAssets
     *
     */
    protected function setAssets()
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
    protected function createUser()
    {
        $params = $this->getParams();
        $params[self::_PROFIL] = 'user';
        $params[self::_STATUS] = 'waiting';
        $params[self::_EMAIL] = strtolower($params[self::_LOGIN]);
        $params[self::_LOGIN] = strtolower($params[self::_LOGIN]);
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
     * getLoginInputFilter
     *
     * @return inputFilter
     */
    protected function getLoginInputFilter($postedDatas)
    {
        return new inputFilter(
            $postedDatas,
            [
            self::_LOGIN => FILTER_SANITIZE_EMAIL,
            self::_PASSWORD => FILTER_SANITIZE_STRING,
            self::_TOKEN => FILTER_SANITIZE_STRING
            ]
        );
    }
}
