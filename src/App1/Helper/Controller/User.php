<?php
/**
 * Description of App1\Helper\Controller\User
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \App1\Model\Users as modelUser;
use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Input\Custom\Filters\Range as inputRange;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;

class User extends basicController
{

    use \App1\Helper\Reuse\Controller;

    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';
    const _ID = modelUser::PARAM_ID;
    const _EMAIL = modelUser::PARAM_EMAIL;
    const _PASSWORD = modelUser::PARAM_PASSWORD;
    const _PROFIL = modelUser::PARAM_PROFIL;
    const _STATUS = modelUser::PARAM_STATUS;
    const _TOKEN = modelUser::PARAM_TOKEN;
    const _LOGIN = modelUser::PARAM_LOGIN;
    const _NAME = 'name';
    const _TITLE = 'title';
    const _ORDER = 'order';
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
    const USER_MESSAGE_REGISTRATION_SUCCESS = 'Enregistrement réussi.';
    const USER_MESSAGE_REGISTRATION_FAILED = 'Adresse email déjà utilisée.';
    const USER_MESSAGE_REGISTRATION_INVALID = 'Les champs obligatoires n\'ont pas été saisis correctement.';
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
        $this->modelConfig = $this->getApp()->getConfig()->getSettings('dbPool');
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
        $isPro = sessionTools::getProfil() === 'pro';
        $authLink = ($isAuth) ? [
            self::_TITLE => 'Logout'
            , 'icon' => 'fa fa-sign-out'
            , 'link' => $this->baseUrl . '/user/logout'
            ] : [
            self::_TITLE => 'Login'
            , 'icon' => 'fa fa-sign-in'
            , 'link' => $this->baseUrl . '/user/login'
            ];

        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $items += [
                [
                    self::_TITLE => 'Acl'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/acl/manage'
                ],
                [
                    self::_TITLE => 'Password'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/user/changepassword'
                ],
                [
                    self::_TITLE => 'Database'
                    , 'icon' => 'fa fa-database'
                    , 'link' => $this->baseUrl . '/database/tablesmysql'
                ],
                [
                    self::_TITLE => 'Probes'
                    , 'icon' => 'fa fa-compass'
                    , 'link' => $this->baseUrl . '/probes/manage'
                ],
            ];
        }

        if ($isAuth) {
            $authItems = [/* [
                  self::_TITLE => 'Bizz Calc'
                  , 'icon' => 'fa fa-calculator'
                  , 'link' => $this->baseUrl . '/business/index'
                  ],[
                  self::_TITLE => 'Bizz Cra'
                  , 'icon' => 'fa fa-calendar'
                  , 'link' => $this->baseUrl . '/business/calendar'
                  ] */];
            $items = array_merge($items, $authItems);
        }

        array_push($items, $authLink);
        $navConfig = [
            self::_TITLE => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => $items
        ];
        return $navConfig;
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
            [self::_TITLE => 'Comptes']
        ) : '';
        $detail = glyphHelper::getLinked(
            glyphHelper::EYE_OPEN,
            $this->baseUrl . '/user/detail/' . $linkDetailId,
            [self::_TITLE => 'Détail']
        );
        $intervenant = glyphHelper::getLinked(
            glyphHelper::FOLDER_OPEN,
            $this->baseUrl . '/intervenant/edit/uid/' . $uid,
            [self::_TITLE => 'Edition']
        );
        return $this->getWidgetLinkWrapper($manage . $detail . $intervenant);
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
            [self::_TITLE => 'Register']
        );
        $lostpasswdLink = glyphHelper::getLinked(
            glyphHelper::LOCK,
            $this->baseUrl . '/user/lostpassword',
            [self::_TITLE => 'Lost password']
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
            array(self::_TITLE => 'Gestion des comptes')
        ) : '';

        $editLink = glyphHelper::getLinked(
            glyphHelper::PENCIL,
            $this->baseUrl . '/user/edit' . $linkEditId,
            array(self::_TITLE => 'Edition du compte')
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
            ['title' => 'Se connecter']
        );
        $registerLink = glyphHelper::getLinked(
            glyphHelper::CERTIFICATE,
            $this->baseUrl . '/user/register',
            ['title' => 'Enregistrement']
        );

        return $this->getWidgetLinkWrapper($loginLink . $registerLink);
    }

    /**
     * getAssist
     *
     * @return array
     */
    protected function getAssist()
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
     * getIndexInputFilter
     *
     * @return inputFilter
     */
    protected function getIndexInputFilter()
    {
        return new inputFilter(
            $this->getParams(),
            [
            self::_ID => new inputRange([
                inputRange::MIN_RANGE => 1,
                inputRange::MAX_RANGE => 10000,
                inputRange::_DEFAULT => 800,
                inputRange::CAST => inputRange::FILTER_INTEGER
                ]),
            self::_EMAIL => FILTER_SANITIZE_STRING
            ]
        );
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
