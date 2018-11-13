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
use Pimvc\Views\Helpers\Fa as faHelper;
use \App1\Helper\Lang\IEntries as ILang;

class Lang extends basicController
{

    use \App1\Helper\Reuse\Controller;

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
    const CHANGE_WRAPPER = 'col-sm-4';
    const CHANGE_PARA = 'text-center';
    const _NAME = 'name';
    const _LABEL = 'label';

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
        $this->locale = $this->getApp()->getLocale();
        $this->getApp()->setTranslator();
        $this->translator = $this->getApp()->getTranslator();
        $this->langs = $this->getApp()->getConfig()->getSettings('app')['langs'];
        $this->request = $this->getApp()->getRequest()->get();
        $this->initAssets();
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
     * getChangeLinks
     *
     * @return string
     */
    protected function getChangeLinks(): string
    {
        $langLinks = '';
        foreach ($this->langs as $lang) {
            $url = $this->baseUrl . '/lang/change/name/' . $lang[self::_NAME];
            $link = '<a href="' . $url . '">' . $lang[self::_LABEL] . '</a>';
            $p = '<p class="' . self::CHANGE_PARA . '">' . $link . '</p>';
            $langLinks .= '<div class="' . self::CHANGE_WRAPPER . '">' . $p . '</div>';
        }
        return $langLinks;
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig()
    {
        $lgIcon = faHelper::getFontClass(faHelper::LANGUAGE);
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
                self::_TEXT => $this->translate(ILang::__HOME),
                self::_ICON => faHelper::getFontClass(faHelper::HOME),
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
}
