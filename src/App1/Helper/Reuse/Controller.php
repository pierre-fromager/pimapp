<?php

namespace App1\Helper\Reuse;

use \Pimvc\Views\Helpers\Widgets\Standart as standartWidget;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \Pimvc\Html\Element\Decorator as htmlElement;
use \App1\Views\Helpers\Bootstrap\Nav as bootstrapNav;
use \App1\Views\Helpers\Layouts\Responsive as responsiveLayout;
use \App1\Tools\Mail\Sender as mailSender;
use \App1\Views\Helpers\Gdpr\Modal as gdprModal;

trait Controller
{

    /**
     * getNavConfig
     */
    abstract protected function getNavConfig(): array;

    /**
     * getNav
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    protected function getNav()
    {
        return (new bootstrapNav)->setParams($this->getNavConfig())->render()->translate();
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
        $layoutParams = [
            'content' => $content,
            'nav' => ($nav) ? $this->getNav() : ''
        ];
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
        return new htmlElement('div', $content, ['style' => 'float:right']);
    }

    /**
     * getListeTableResponsive
     *
     * @param mixed $liste
     * @return string
     */
    protected function getListeTableResponsive($liste): string
    {
        $stringList = ($liste instanceof \Pimvc\Liste ) ? (string) $liste : $liste;
        return new htmlElement('div', $stringList, ['class' => 'table-responsive']);
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
     * translate
     *
     * @param string $key
     * @return string
     */
    protected function translate(string $key): string
    {
        return $this->getApp()->translator->translate($key);
    }

    /**
     * transMark
     *
     * @param string $key
     * @return string
     */
    protected function transMark(string $key): string
    {
        return bootstrapNav::transMark($key);
    }

    /**
     * getAssist
     *
     * @return array
     */
    protected function getAssist($assistName)
    {
        return sessionAssistTools::getSearch(
            $assistName,
            $this->getApp()->getRequest(),
            $this->getParams(self::PARAM_RESET)
        );
    }

    /**
     * menuAction
     *
     * @param string $title
     * @param string $icon
     * @param string $action
     * @return array
     */
    protected function menuAction($title, $icon, $action)
    {
        return bootstrapNav::menuAction($title, $icon, $action);
    }

    /**
     * sendMail
     *
     * @param string $from
     * @param string $to
     * @param string $subject
     * @param string $content
     * @return string
     */
    protected function sendMail(string $from, string $to, string $subject, string $content): string
    {
        $mailSender = (new mailSender)->setFrom($from)->setTo($to)->setSubject($subject)->setBody($content);
        $mailError = '';
        try {
            $mailSender->send();
        } catch (\Exception $ex) {
            $mailError = $ex->getMessage();
        }
        return $mailError;
    }

    /**
     * getConfigSettings
     *
     * @param type $entry
     * @return array
     */
    protected function getConfigSettings(string $entry)
    {
        return $this->getApp()->getConfig()->getSettings($entry);
    }

    /**
     * getAcls
     *
     * @return array
     */
    protected function getAcls(): array
    {
        return $this->getApp()->middlewareItems['acl']->getRessources();
    }

    /**
     * getCalledNamespace
     *
     * @return string
     */
    protected function getCalledNamespace(): string
    {
        $nsParts = explode('\\', static::class);
        array_pop($nsParts);
        return implode('\\', $nsParts);
    }
    
    /**
     * getGdprModal
     *
     * @return string
     */
    protected function getGdprModal(): string
    {
        $accepted = (isset($_COOKIE['rgpd_accepted']) && ($_COOKIE['rgpd_accepted'] == 1));
        return ($accepted) ? '' : (string) (new gdprModal)->render();
    }
}
