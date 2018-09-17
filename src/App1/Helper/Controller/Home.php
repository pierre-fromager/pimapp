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
        $authLink = ($isAuth) ? [
            'title' => 'Logout'
            , 'icon' => 'fa fa-sign-out'
            , 'link' => $this->baseUrl . '/user/logout'
            ] : [
            'title' => 'Login'
            , 'icon' => 'fa fa-sign-in'
            , 'link' => $this->baseUrl . '/user/login'
            ];
        $freeItems = [
            [
                'title' => 'Lignes'
                , 'icon' => 'fa fa-subway'
                , 'link' => $this->baseUrl . '/metro/lignes/manage'
            ]
        ];
        $items = $items = array_merge($items, $freeItems);
        if ($isAdmin) {
            $items += [
                [
                    'title' => 'Acl'
                    , 'icon' => 'fa fa-lock'
                    , 'link' => $this->baseUrl . '/acl/manage'
                ],
                [
                    'title' => 'Database'
                    , 'icon' => 'fa fa-database'
                    , 'link' => $this->baseUrl . '/database/tablesmysql'
                ],
                [
                    'title' => 'Probes'
                    , 'icon' => 'fa fa-compass'
                    , 'link' => $this->baseUrl . '/probes/manage'
                ],
            ];
        }
        if ($isAuth) {
            $authItems = [
                [
                    'title' => 'User'
                    , 'icon' => 'fa fa-user'
                    , 'link' => $this->baseUrl . '/user/edit'
                ]];
            $items = array_merge($items, $authItems);
        }
        array_push($items, $authLink);
        $navConfig = [
            'title' => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => $items
        ];
        return $navConfig;
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
