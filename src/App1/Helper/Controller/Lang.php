<?php

/**
 * Description of App1\Helper\Controller\Lang
 *
 * @author Pierre Fromager
 */

namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use App1\Tools\Lang as toolsLang;
use Pimvc\Tools\Session as sessionTools;
use Pimvc\Views\Helpers\Collection\Css as cssCollection;
use Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;

class Lang extends basicController
{

    const ERROR_READING = 'Une erreur s\'est produite lors de la lecture, merci de vérifier le fichier (entetes, données)';
    const CSV_DONE = 'Les données ont bien été enregistrées';
    const IMPORT_PARTIAL_NAME = 'lang_import.html';
    const UPLOAD_MAX_FILESIZE = 2097152;
    const EXPORT_NO_DATA_FOUND = 'Aucune donnée trouvée pour la langue demandée';
    const _CHANGE_ACTION = '/lang/change';
    const _IMPORT_ACTION = '/lang/import';
    const _EXPORT_ACTION = '/lang/export';
    const LANG_REFERER = 'HTTP_REFERER';
    const _FILENAME = 'filename';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';
    const LAYOUT_NAME = 'responsive';
    const PUBLIC_CSS = '/public/css/';
    const PUBLIC_JS = '/public/js/';

    protected $langs = [];
    protected $locale;
    protected $translator;
    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->getApp()->setLocale('ru-RU');
        $this->locale = $this->getApp()->getLocale();
        $this->getApp()->setTranslator();
        $this->translator = $this->getApp()->getTranslator();
        $this->langs = $this->getApp()->getConfig()->getSettings('app')['langs'];
        $this->request = $this->getApp()->getRequest()->get();
        $this->initAssets();
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
     * getWidget
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    protected function getWidget($title, $content)
    {
        return (new widgetHelper())
                        ->setTitle($title)
                        ->setBody((string) $content)
                        ->render();
    }

    /**
     * getNav
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    protected function getNav()
    {
        return (new \App1\Views\Helpers\Bootstrap\Nav())
                        ->setParams($this->getNavConfig())
                        ->render();
    }

    /**
     * getLayout
     *
     * @param string $content
     * @return \App1\Views\Helpers\Layouts\Responsive
     */
    protected function getLayout($content, $nav = true)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => ($nav) ? $this->getNav() . $content : $content];
        $layout->setApp($this->getApp())
                ->setName(self::LAYOUT_NAME)
                ->setLayoutParams($layoutParams)
                ->build();
        return $layout;
    }

    /**
     * getLangPath
     *
     * @return string
     */
    protected static function getLangPath()
    {
        return toolsLang::getLangPath() . self::$lang . self::_ECSV;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    private function getNavConfig()
    {
        $lgIcon = 'fa fa-language';
        $freeItems = [
            $this->menuAction('Change lang', $lgIcon, self::_CHANGE_ACTION),
        ];
        $items = array_merge([], $freeItems);
        $isAdmin = sessionTools::isAdmin();
        if ($isAdmin) {
            $adminItems = [
                $this->menuAction('Import lang', $lgIcon, self::_IMPORT_ACTION),
                $this->menuAction('Export lang', $lgIcon, self::_EXPORT_ACTION),
            ];
            $items = array_merge($items, $adminItems);
        }
        return [
            self::_TITLE => [
                self::_TEXT => 'Pimapp',
                self::_ICON => 'fa fa-home',
                self::_LINK => $this->baseUrl
            ],
            self::_ITEMS => $items
        ];
    }

    /**
     * initAssets
     *
     */
    private function initAssets()
    {
        $cssAssets = ['tables/table-6.css', 'widget.css'];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add(self::PUBLIC_CSS . $cssAssets[$c]);
        }
        cssCollection::save();
        jsCollection::add(self::PUBLIC_JS . 'sortable.js');
        jsCollection::save();
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
