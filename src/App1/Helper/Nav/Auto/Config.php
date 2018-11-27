<?php
namespace App1\Helper\Nav\Auto;

use \Pimvc\Views\Helpers\Fa as faHelper;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Acl as aclTools;
use \Pimvc\Controller as AbstractController;
use \App1\Helper\Lang\IEntries as ILang;
use \App1\Views\Helpers\Bootstrap\Nav;
use \App1\Helper\Nav\Icon;
use \App1\Helper\Nav\Title;

class Config
{

    const _ACL = 'acl';
    const _LOGIN = 'login';
    const _LOGOUT = 'logout';
    const API_PREFIX = 'App1\Controller\Api\\';
    const MATCH_START = '/(';
    const MATCH_GLUE = ')|(';
    const MATCH_END = ')/';
    const DELIM = '\\';
    const SL = '/';

    protected $actionFilter;
    protected $acls;
    protected $config;
    protected $app;
    protected $baseUrl;

    /**
     * __construct
     *
     * @return $this
     */
    public function __construct()
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->actionFilter = [];
        return $this;
    }

    /**
     * setFilter
     *
     * @param array $filter
     * @return $this
     */
    public function setFilter(array $filter)
    {
        $this->actionFilter = $filter;
        return $this;
    }

    /**
     * render
     *
     * @return $this
     */
    public function render()
    {
        $this->prepareAcls();
        $this->config = [];
        $p = sessionTools::getProfil();
        $cnsLen = $this->getControllerRootNamespaceLen() + 1;
        foreach ($this->acls as $controller => $actions) {
            foreach ($actions as $action => $roles) {
                if ($roles[$p] == aclTools::ACL_ALLOW) {
                    $link = self::SL . strtolower(
                        substr(
                            str_replace(self::DELIM, self::SL, $controller),
                            $cnsLen
                        ) . self::SL . $action
                    );
                    if ($this->matchFilter($link)) {
                        $this->config[] = Nav::menuAction(
                            Title::get($link),
                            Icon::get($link),
                            $link,
                            $this->baseUrl
                        );
                    }
                }
            }
        }
        $this->setAuthAction();
        return $this;
    }

    /**
     * getConfig
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            Nav::_TITLE => [
                Nav::_TEXT => Nav::transMark(ILang::__HOME),
                Nav::_ICON => faHelper::getFontClass(faHelper::HOME),
                Nav::_LINK => $this->baseUrl
            ],
            Nav::_ITEMS => $this->config
        ];
    }

    /**
     * setAuthAction
     *
     */
    protected function setAuthAction()
    {
        $isAuth = sessionTools::isAuth();
        $authAction = ($isAuth) ? self::_LOGOUT : self::_LOGIN;
        $authLink = '/user/' . $authAction;
        $authIcon = Icon::get($authLink);
        $authTitle = Nav::transMark($isAuth ? ILang::__LOGOUT : ILang::__LOGIN);
        $this->config[] = Nav::menuAction(
            $authTitle,
            $authIcon,
            $authLink,
            $this->baseUrl
        );
    }

    /**
     * prepareAcls
     *
     */
    protected function prepareAcls()
    {
        $this->acls = $this->getAcls();
        $apis = array_filter(array_keys($this->acls), function ($v) {
            return strpos($v, self::API_PREFIX) !== false;
        });
        foreach ($apis as $api) {
            unset($this->acls[$api]);
        }
        unset(
            $this->acls[\App1\Controller\Error::class],
            $this->acls[\App1\Controller\Home::class],
            $this->acls[\App1\Controller\User::class][self::_LOGIN],
            $this->acls[\App1\Controller\User::class][self::_LOGOUT]
        );
    }

    /**
     * getAcls
     *
     * @return array
     */
    protected function getAcls(): array
    {
        return $this->app->middlewareItems[self::_ACL]->getRessources();
    }

    /**
     * matchFilter
     *
     * @param type $action
     * @return bool
     */
    protected function matchFilter($action): bool
    {
        $regAction = implode($this->actionFilter, self::MATCH_GLUE);
        return preg_match(
            self::MATCH_START . $regAction . self::MATCH_END,
            $action
        );
    }

    /**
     * getControllerRootNamespaceLen
     *
     * @return string
     */
    protected function getControllerRootNamespaceLen(): int
    {
        $rootNs = explode('\\', static::class)[0] . '\\' . AbstractController::_NAMESPACE;
        return strlen($rootNs);
    }
}
