<?php

namespace App1\Helper\Reuse;

use \Pimvc\Views\Helpers\Widgets\Standart as standartWidget;
use \App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
use \App1\Views\Helpers\Layouts\Responsive as responsiveLayout;
use \Pimvc\Tools\Session as sessionTools;

trait Controller
{

    /**
     * getNavConfig
     */
    abstract protected function getNavConfig();

    /**
     * getNav
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    protected function getNav()
    {
        return (new bootstrapNav)->setParams($this->getNavConfig())->render();
    }

    /**
     * getWidget
     *
     * @return Pimvc\Views\Helpers\Widget
     */
    protected function getWidget($title, $content, $id = '')
    {
        $widget = (new standartWidget())->setTitle($title);
        if ($id) {
            $widget->setBodyOptions(['id' => $id, 'class' => 'body']);
        }
        $widget->setBody((string) $content);
        $widget->render();
        return (string) $widget;
    }

    /**
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    protected function getLayout($content, $nav = true)
    {
        $layoutParams = ['content' => $content];
        $layoutParams['nav'] = ($nav) ? $this->getNav() : '';

        return (new responsiveLayout)
                        ->setApp($this->getApp())
                        ->setName(self::LAYOUT_NAME)
                        ->setLayoutParams($layoutParams)
                        ->build();
    }

    /**
     * isPost
     *
     * @return boolean
     */
    protected function isPost()
    {
        return ($this->getApp()->getRequest()->getMethod() === 'POST');
    }

    /**
     * getWidgetLinkWrapper
     *
     * @param string $content
     * @return string
     */
    protected function getWidgetLinkWrapper(string $content): string
    {
        return '<div style="float:right">' . $content . '</div>';
    }

    /**
     * getListeTableResponsive
     *
     * @param \Pimvc\Liste $liste
     * @return string
     */
    protected function getListeTableResponsive(\Pimvc\Liste $liste): string
    {
        return '<div class="table-responsive">' . (string) $liste . '</div>';
    }

    /**
     * setPageSize
     *
     */
    protected function setPageSize()
    {
        if ($this->getParams(self::_PAGESIZE)) {
            sessionTools::set(
                self::_PAGESIZE,
                $this->getParams(self::_PAGESIZE)
            );
        }
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
}
