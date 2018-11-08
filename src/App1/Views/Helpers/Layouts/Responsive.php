<?php

/**
 * Description of App1\Views\Helpers\Layouts\Responsive
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Layouts;

use \App1\Views\Helpers\Layouts\Interfaces\Responsive as responsiveLayoutInterface;

class Responsive extends \Pimvc\Layout implements responsiveLayoutInterface
{
    protected $path;
    protected $layoutParams = [];
    protected $app;
    protected $name;
    protected $config;


    /**
     * __construct
     *
     * @param string $name
     * @return $this
     */
    public function __construct()
    {
        parent::__construct();
        return $this;
    }

    /**
     * setApp
     *
     * @param type $app
     * @return $this
     */
    public function setApp(\Pimvc\App $app)
    {
        $this->app = $app;
        $this->path = $this->app->getPath() . self::LAYOUT_PATH . DIRECTORY_SEPARATOR;
        $this->layoutConfig = $this->app
                        ->getConfig()
                        ->getSettings(self::PARAM_HTML)[self::PARAM_LAYOUT_CONFIG];
        $this->htmlParts = $this->getHtmlParts();
        return $this;
    }

    /**
     * getLayoutBaseUrl
     *
     * @return string
     */
    private function getLayoutBaseUrl()
    {
        return $this->app->getRequest()->getBaseUrl() . DIRECTORY_SEPARATOR;
    }

    /**
     * getLayoutParams
     *
     * @return array
     */
    public function getLayoutParams()
    {
        return [
            self::PARAM_HEADER => [
                self::PARAM_DOCTYPE => $this->layoutConfig[self::PARAM_DOCTYPE],
                self::PARAM_DESCRIPTION => $this->layoutConfig[self::PARAM_DESCRIPTION],
                self::PARAM_PUBLISHER => $this->layoutConfig[self::PARAM_PUBLISHER],
                self::PARAM_REVISITAFTER => $this->layoutConfig[self::PARAM_REVISITAFTER],
                self::PARAM_COPYRIGHT => $this->layoutConfig[self::PARAM_COPYRIGHT],
                self::PARAM_AUTHOR => $this->layoutConfig[self::PARAM_AUTHOR],
                self::PARAM_ORGANIZATION => $this->layoutConfig[self::PARAM_ORGANIZATION],
                self::PARAM_KEYWORDS => $this->layoutConfig[self::PARAM_KEYWORDS],
                self::PARAM_ROOT_URL => '', //BASE_URI,
                // App1\Views\Helpers\Collection\
                self::PARAM_BASEURL => $this->getLayoutBaseUrl(),
                self::PARAM_TITLE => $this->layoutConfig[self::PARAM_TITLE],
            ], self::PARAM_BODY => [
                self::PARAM_REQUEST => $this->app->getRequest(),
                self::PARAM_BREADCRUMB => '', //Helper_Breadcrumb::get(),
                'langSelector' => '', //(string) new Helper_Lang(),
                'nav' => (isset($this->layoutParams['nav'])) ? (string) $this->layoutParams['nav'] : '',
                self::PARAM_CONTENT => (isset($this->layoutParams[self::PARAM_CONTENT]))
                    ? (string) $this->layoutParams[self::PARAM_CONTENT]
                    : '',
                self::PARAM_BASEURL => $this->app->getRequest()->getBaseUrl(),
                'searchValue' => '', //$request->getParam('searchmotif'),
                'serviceMenu' => '', //(string) new Helper_Slicknavmenu(),
                'needPresence' => '', //($controllerName !== 'ulto'),
                self::PARAM_CLOUD => '', //isset($frontValues[self::PARAM_CLOUD])
                                        //*? $frontValues[self::PARAM_CLOUD] : '',
            ],
            self::PARAM_FOOTER => [
                self::PARAM_BASEURL => $this->app->getRequest()->getBaseUrl(),
                self::PARAM_COPYRIGHT => $this->layoutConfig[self::PARAM_COPYRIGHT],
                self::PARAM_ORGANIZATION => $this->layoutConfig[self::PARAM_ORGANIZATION],
                self::PARAM_STREET => $this->layoutConfig[self::PARAM_STREET],
                self::PARAM_POCODE => $this->layoutConfig[self::PARAM_POCODE],
                self::PARAM_CITY => $this->layoutConfig[self::PARAM_CITY],
                self::PARAM_COUNTRY => $this->layoutConfig[self::PARAM_COUNTRY],
                self::PARAM_EMAIL => $this->layoutConfig[self::PARAM_EMAIL],
                self::PARAM_DATE => date(self::DATE_FORMAT),
                self::PARAM_ELLAPSE => '', //profiling::getEllpase('stop','start')
            ]
        ];
    }
}
