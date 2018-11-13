<?php
/**
 * Description of App1\Helper\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;
use Pimvc\Views\Helpers\Fa as faHelper;
use \App1\Helper\Lang\IEntries as ILang;

class Home extends basicController implements ILang
{

    use \App1\Helper\Reuse\Controller;

    const PARAM_HTML = 'html';
    const PARAM_NAV = 'nav';
    const PARAM_CAROUSEL = 'carousel';
    const LAYOUT_NAME = 'responsive';
    const VIEW_INDEX = '/Views/Home/Index.php';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';

    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig()
    {
        $isAuth = sessionTools::isAuth();
        $isAdmin = sessionTools::isAdmin();
        $items = [];
        $authLink = $this->menuAction(
            ($isAuth) ? $this->translate(ILang::__LOGOUT) : $this->translate(ILang::__LOGIN),
            ($isAuth) ? faHelper::getFontClass(faHelper::SIGN_OUT) : faHelper::getFontClass(faHelper::SIGN_IN),
            ($isAuth) ? '/user/logout' : '/user/login'
        );
        $freeItems = [
            $this->menuAction(
                $this->translate(ILang::__LANG),
                faHelper::getFontClass(faHelper::LANGUAGE),
                '/lang/change'
            ),
            $this->menuAction(
                $this->translate(ILang::__TRAIN),
                faHelper::getFontClass(faHelper::SUBWAY),
                '/metro/lignes/manage'
            )
        ];
        $items = array_merge($items, $freeItems);
        if ($isAdmin) {
            $adminItems = [
                $this->menuAction(
                    $this->translate(ILang::__PERMISSIONS),
                    faHelper::getFontClass(faHelper::LOCK),
                    '/acl/manage'
                ),
                $this->menuAction(
                    $this->translate(ILang::__DATABASE),
                    faHelper::getFontClass(faHelper::DATABASE),
                    '/database/tablesmysql'
                ),
                $this->menuAction(
                    $this->translate(ILang::__SENSORS),
                    faHelper::getFontClass(faHelper::COMPASS),
                    '/probes/manage'
                )
            ];
            $items = array_merge($items, $adminItems);
        }
        if ($isAuth) {
            $authItems = [
                $this->menuAction(
                    $this->translate(ILang::__USERS),
                    faHelper::getFontClass(faHelper::USER),
                    '/user/edit'
                )
            ];
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
     * menuAction
     *
     * @param string $title
     * @param string $icon
     * @param string $action
     * @return array
     */
    private function menuAction($title, $icon, $action)
    {
        return [
            self::_TITLE => ucfirst($title)
            , self::_ICON => $icon
            , self::_LINK => $this->baseUrl . $action
        ];
    }
}
