<?php

/**
 * class App1\Helper\Controller\Database
 *
 * is a controller for database table description and code generation.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */

namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;
use Pimvc\Views\Helpers\Collection\Css as cssCollection;
use Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \App1\Views\Helpers\Bootstrap\Button as bootstrapButton;
use \Pimvc\Views\Helpers\Widgets\Standart as widgetHelper;
use Pimvc\Helper\Model\Mysql as mysqlModelHelper;
use Pimvc\Helper\Model\Fourd as fourdModelHelper;
use Pimvc\Helper\Model\Pgsql as pgsqlModelHelper;
use Pimvc\Helper\Model\IHelper as interfaceModelHelper;

class Database extends basicController implements interfaceModelHelper
{

    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';
    const _ID = 'id';
    const PARAM_TABLES_4D = 'tables-4d';
    const LAYOUT_NAME = 'responsive';
    const LABEL_GENERATE_CODE = 'Code';

    protected $baseUrl = '';
    protected $request = null;
    protected $indexes = array();
    protected $indexesType = array();
    protected $columns = array();
    protected $relations = array();
    protected $tableList = array();
    protected $currentTableName = '';
    protected $consColumns = array();
    private $modelConfig;
    protected $adapter;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getApp()->getConfig()->getSettings('dbPool');
        $this->request = $this->getApp()->getRequest();
        $this->baseUrl = $this->request->getBaseUrl();
        $this->initAssets();
        $actionName = $this->getApp()->getController()->getAction();
        $this->setAdapterFromAction($actionName);
        $this->setTableList();
        if ($this->hasValue(self::_ID)) {
            $id = $this->getParams(self::_ID);
            $tableListIds = array_flip($this->tableList);
            $this->currentTableName = $tableListIds[$id];
            unset($tableListIds);
            if ($this->adapter == \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D) {
                $this->init4d($id, $actionName);
            } elseif ($this->adapter == \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL) {
                $this->initMysql($id, $actionName);
            } elseif ($this->adapter == \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL) {
                $this->initPgsql($id, $actionName);
            }
        }
    }

    /**
     * initAssets
     *
     */
    private function initAssets()
    {
        $cssPath = '/public/css/';
        $cssAssets = ['tables/table-6.css', 'widget.css', 'spinkit/cube-grid.css'];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add($cssPath . $cssAssets[$c]);
        }
        cssCollection::save();
        $jsPath = '/public/js/';
        $jsAssets = ['sortable.js'];
        for ($c = 0; $c < count($jsAssets); $c++) {
            jsCollection::add($jsPath . $jsAssets[$c]);
        }
        jsCollection::save();
    }

    /**
     * init4d
     *
     * @param string $id
     * @param string $actionName
     */
    private function init4d($id, $actionName)
    {
        $modelHelper = new fourdModelHelper($id);
        $this->indexesType = $modelHelper->getIndexesType();
        $this->indexes = $modelHelper->getIndexes();
        $this->consColumns = $modelHelper->getConscolumns();
        $relationWithLink = ($actionName == 'tables4d');
        $this->relations = $modelHelper->getRelations($relationWithLink);
        $this->columns = $modelHelper->getColumns(!$relationWithLink);
    }

    /**
     * initMysql
     *
     * @param string $id
     * @param string $actionName
     */
    private function initMysql($id, $actionName)
    {
        $this->currentTableName = $this->getParams(self::_ID);
        $modelHelper = new mysqlModelHelper($id);
        $this->indexes = $modelHelper->getIndexes();
        $relationWithLink = ($actionName == 'tablesmysql');
        $this->relations = $modelHelper->getRelations($relationWithLink);
        $this->columns = $modelHelper->getColumns(!$relationWithLink);
    }

    /**
     * initPgsql
     *
     * @param string $id
     * @param string $actionName
     */
    private function initPgsql($id, $actionName)
    {
        $this->currentTableName = $this->getParams(self::_ID);
        $modelHelper = new pgsqlModelHelper($id);
        $this->indexes = $modelHelper->getIndexes();
        $relationWithLink = ($actionName == 'tablespgsql');
        // $this->relations = $modelHelper->getRelations($relationWithLink);
        $this->columns = $modelHelper->getColumns(!$relationWithLink);
    }

    /**
     * setAdapterFromAction
     *
     * @param string $action
     */
    protected function setAdapterFromAction($action)
    {
        if (strpos($action, 'mysql') !== false) {
            $this->adapter = \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL;
        } elseif (strpos($action, 'pgsql') !== false) {
            $this->adapter = \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL;
        } elseif (strpos($action, '4d') !== false) {
            $this->adapter = \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D;
        }
    }

    /**
     * setTableList
     *
     */
    protected function setTableList()
    {
        switch ($this->adapter) {
            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL:
                $tablesModel = new \Pimvc\Model\Users($this->modelConfig);
                $tables = $tablesModel->showTables();
                $this->tableList = array_combine($tables, $tables);
                break;
            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D:
                $tablesModel = new \Pimvc\Model\Fourd\Tables($this->modelConfig);
                $this->tableList = $tablesModel->getPair();
                break;
            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL:
                $tablesModel = new \Pimvc\Model\Pgsql\Tables($this->modelConfig);
                $this->tableList = $tablesModel->getPair();
                break;
        }
        unset($tablesModel);
    }

    /**
     * getConscolumn
     *
     * @param string $contrainName
     * @return array
     */
    protected function getConscolumn($contrainName)
    {
        return (isset($this->consColumns[$contrainName])) ? $this->consColumns[$contrainName] : '';
    }

    /**
     * getIndexType
     *
     * @param string $indexId
     * @return array
     */
    protected function getIndexType($indexId)
    {
        return $this->indexesType[$indexId];
    }

    /**
     * getButton
     *
     * @param string $label
     * @param string $link
     * @return string
     */
    protected function getButton($label, $link)
    {
        $button = new bootstrapButton($label);
        $button->setDatalink($link)
                ->setType($button::TYPE_BLOCK)
                ->setExtraClass($button::TYPE_SUCCESS)
                ->render();
        return (string) $button;
    }

    /**
     * getViewPath
     *
     * @param string $actionName
     * @return string
     */
    protected function getViewPath($actionName)
    {
        return $this->getApp()->getPath() . self::VIEW_DATABASE_PATH
                . ucfirst($actionName) . '.php';
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
        ];
        $items = array_merge($items, $freeItems);
        if ($isAdmin) {
            $adminItems = [
                $this->menuAction('Mysql', 'fa fa-database', '/database/tablesmysql'),
                $this->menuAction('Pgsql', 'fa fa-database', '/database/tablespgsql'),
                //$this->menuAction('4d', 'fa fa-database', '/database/tables4d'),
                $this->menuAction('Crud', 'fa fa-cog', '/crud'),
                $this->menuAction('Csv upload', 'fa fa-file', '/database/uploadcsv'),
                $this->menuAction('Csv import', 'fa fa-file-text', '/database/importcsv'),
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
                self::_TEXT => 'Home',
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
     * getWidget
     *
     * @return Pimvc\Views\Helpers\Widget
     */
    protected function getWidget($title, $content, $id = '')
    {
        $widget = (new widgetHelper())->setTitle($title);
        if ($id) {
            $widget->setBodyOptions(['id' => $id, 'class' => 'body']);
        }
        $widget->setBody((string) $content);
        $widget->render();
        return (string) $widget;
    }

    /**
     * getNav
     *
     * @return \App1\Views\Helper\Bootstrap\Nav
     */
    protected function getNav()
    {
        $nav = (new \App1\Views\Helpers\Bootstrap\Nav());
        $nav->setParams($this->getNavConfig())->render();
        return $nav;
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
        $layoutParams = ['content' => $content];
        if ($nav) {
            $layoutParams['nav'] = $this->getNav();
        }
        $layout->setApp($this->getApp())
                ->setName(self::LAYOUT_NAME)
                ->setLayoutParams($layoutParams)
                ->build();
        return $layout;
    }
}
