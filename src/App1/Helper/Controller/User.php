<?php
/**
 * Description of App1\Helper\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \Pimvc\Input\Filter as inputFilter;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Input\Custom\Filters\Range as inputRange;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;

class User extends basicController
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
        $this->userModel = new \App1\Model\Users($this->modelConfig);
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
                [
                    self::PARAM_TITLE => 'Probes'
                    , 'icon' => 'fa fa-compass'
                    , 'link' => $this->baseUrl . '/probes/manage'
                ],
            ];
        }

        if ($isAuth) {
            $authItems = [/* [
                      self::PARAM_TITLE => 'Bizz Calc'
                      , 'icon' => 'fa fa-calculator'
                      , 'link' => $this->baseUrl . '/business/index'
                      ],[
                      self::PARAM_TITLE => 'Bizz Cra'
                      , 'icon' => 'fa fa-calendar'
                      , 'link' => $this->baseUrl . '/business/calendar'
                      ] */];
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
    protected function setPageSize()
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
    protected function getIndexInputFilter()
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
    protected function getLoginInputFilter($postedDatas)
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

    /**
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    protected function getLayout($content)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => $content];
        $layout->setApp($this->getApp())
                ->setName(self::LAYOUT_NAME)
                ->setLayoutParams($layoutParams)
                ->build();
        return $layout;
    }
}
