<?php
/**
 * Description of App1\Helper\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;

class Home extends basicController
{

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
            ($isAuth) ? 'Logout' : 'Login',
            ($isAuth) ? 'fa fa-sign-out' : 'fa fa-sign-in',
            ($isAuth) ? '/user/logout' : '/user/login'
        );
        $freeItems = [
            $this->menuAction('Change lang', 'fa fa-language', '/lang/change'),
            $this->menuAction('Lignes', 'fa fa-subway', '/metro/lignes/manage')
        ];
        $items = array_merge($items, $freeItems);
        if ($isAdmin) {
            $adminItems = [
                $this->menuAction('Acl', 'fa fa-lock', '/acl/manage'),
                $this->menuAction('Database', 'fa fa-database', '/database/tablesmysql'),
            ];
            $items = array_merge($items, $adminItems);
        }
        if ($isAuth) {
            $authItems = [
                $this->menuAction('User', 'fa fa-user', '/user/edit'),
            ];
            $items = array_merge($items, $authItems);
        }
        array_push($items, $authLink);
        $navConfig = [
            self::_TITLE => [
                self::_TEXT => 'Pimapp',
                self::_ICON => 'fa fa-home',
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
            self::_TITLE => $title
            , self::_ICON => $icon
            , self::_LINK => $this->baseUrl . $action
        ];
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
        $layoutParams = ['content' => (string) $content];
        $layout->setApp($this->getApp())
            ->setName(self::LAYOUT_NAME)
            ->setLayoutParams($layoutParams)
            ->build();
        return $layout;
    }
}
