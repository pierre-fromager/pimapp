<?php

/**
 * Description of App1\Helper\Controller\Crud
 *
 * is a helper controller for Crud.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */

namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Views\Helpers\Toolbar\Glyph as glyphToolbar;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use \Pimvc\Helper\Model\IHelper as interfaceModelHelper;
use \Pimvc\Tools\Assist\Session as sessionAssistTools;
use \App1\Helper\Nav\Auto\Config as autoNavConfig;

class Crud extends basicController implements interfaceModelHelper
{
    use \App1\Helper\Reuse\Controller;

    const _ID = 'id';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';
    const ASSIST_CRUD_SELECT = 'assist-crud-select';
    const ASSIST_CRUD_SEARCH = 'assist-crud-search';
    const _RESET = 'reset';
    const _PAGESIZE = 'pagesize';
    const _PAGE = 'page';
    const _SLOT = 'slot';
    const _TABLE = 'table';
    const _ADAPTER = 'adapter';
    const LAYOUT_NAME = 'responsive';
    const VIEW_INDEX = '/Views/Crud/Select.php';

    protected $baseUrl;
    protected $request;
    protected $modelConfig;
    protected $adapter;
    protected $table;
    protected $slot;
    protected $crudInstance;
    protected $fields;
    protected $tableExists;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getApp()->getConfig()->getSettings('dbPool');
        $this->modelConfig['lateDomain'] = true;
        $this->request = $this->getApp()->getRequest();
        $this->baseUrl = $this->request->getBaseUrl();
        $this->initAssets();
        $this->setConfig();
    }

    /**
     * setConfig
     *
     */
    protected function setConfig()
    {
        $actionName = $this->getApp()->getController()->getAction();
        $isSelectAction = ($actionName == 'Index');
        if ($isSelectAction) {
            $hasSlot = $this->hasValue(self::_SLOT);
            $hasTable = $this->hasValue(self::_TABLE);
            if ($hasSlot) {
                $this->slot = $this->getParams(self::_SLOT);
            } else {
                $slotList = array_keys($this->modelConfig);
                $this->slot = $slotList[0];
            }
            if ($hasTable) {
                $this->table = $this->getParams(self::_TABLE);
                $this->getSelectAssist();
            }
        } else {
            $assist = sessionAssistTools::getDatas(self::ASSIST_CRUD_SELECT);
            $this->slot = $assist[self::_SLOT];
            $this->table = $assist[self::_TABLE];
        }
        $this->adapter = $this->modelConfig[$this->slot][self::_ADAPTER];
        if ($this->slot && $this->adapter && $this->table) {
            $this->setTableExists();
            if ($this->tableExists) {
                $this->setCrudInstance();
            }
        }
    }

    /**
     * getFields
     *
     * @return \Pimvc\Db\Model\Fields
     */
    protected function getFields(): \Pimvc\Db\Model\Fields
    {
        $fields = new \Pimvc\Db\Model\Fields();
        $desc = [];
        if ($this->table && $this->slot) {
            $forge = new \Pimvc\Db\Model\Forge($this->slot);
            $descs = $forge->describeTable($this->table);
            $indexes = $forge->getIndexes($this->table);
            list($columnName, $columnPrimary, $columnPrimaryValue, $descName) = $this->fieldFactory();
            $indexeNames = array_map(function ($v) use ($columnName) {
                return $v[$columnName];
            }, $indexes);

            $pkFilter = array_filter($indexes, function ($v) use ($columnPrimary, $columnPrimaryValue) {
                return ($v[$columnPrimary] === $columnPrimaryValue);
            });
            $pkNames = array_map(function ($v) use ($columnName) {
                return $v[$columnName];
            }, $pkFilter);

            foreach ($descs as $desc) {
                $f = new \Pimvc\Db\Model\Field();
                $isKey = in_array($desc[$descName], $indexeNames);
                $isPrimary = in_array($desc[$descName], $pkNames);
                $desc[\Pimvc\Db\Model\Field::_PRIMARY] = $isPrimary;
                $desc[\Pimvc\Db\Model\Field::_KEY] = $isKey;
                $f->setFromDescribe($this->adapter, $desc);
                $fields->addItem($f);
                unset($f);
            }
        }
        return $fields;
    }

    /**
     * getListe
     *
     * @param \App1\Model\Crud $crudInstance
     * @param array $criterias
     * @param array $fieldList
     * @return \Pimvc\Liste
     */
    protected function getListe(\App1\Model\Crud $crudInstance, array $criterias, array $fieldList)
    {
        $liste = new \Pimvc\Liste(
            $crudInstance,
            'crud/manage',
            [],
            $this->getListToolbar(),
            $this->getParams(self::_PAGE),
            $criterias,
            $fieldList,
            ['order' => 'desc']
        );
        if (!sessionTools::isAdmin()) {
            $whereConditions = ['key' => self::_ID, 'operator' => '>', 'value' => 0];
            $conditions = [
                glyphToolbar::EXCLUDE_EDIT => $whereConditions,
                glyphToolbar::EXCLUDE_CLONE => $whereConditions,
                glyphToolbar::EXCLUDE_DELETE => $whereConditions,
            ];
            $liste->setActionCondition($conditions);
        }
        if ($this->hasValue('context')) {
            return $this->getJsonResponse($liste->getJson());
        }
        return $liste->setShowSql(true)->render();
    }

    /**
     * setCrudInstance
     *
     * @param \Pimvc\Db\Model\Fields $fields
     * @return $this
     */
    protected function setCrudInstance()
    {
        $this->fields = $this->getFields();
        $this->crudInstance = new \App1\Model\Crud(
            $this->slot,
            $this->adapter,
            $this->removeSchemaFromName($this->table),
            $this->modelConfig
        );
        $this->crudInstance->setDomainInstance(
            new \App1\Model\Domain\Crud($this->fields)
        );
        return $this;
    }
    
    /**
     * getEditLinks
     *
     * @return string
     */
    protected function getEditLinks()
    {
        $linkSelect = glyphHelper::getLinked(
            glyphHelper::COG,
            $this->getSelectLink(),
            [self::_TITLE => 'Crud select database table']
        );
        $linkSearch = glyphHelper::getLinked(
            glyphHelper::SEARCH,
            $this->getSearchLink(),
            [self::_TITLE => 'Crud manage']
        );
        return $this->getWidgetLinkWrapper($linkSelect . $linkSearch);
    }

    /**
     * getManageLinks
     *
     * @return string
     */
    protected function getManageLinks()
    {
        $linkSelect = glyphHelper::getLinked(
            glyphHelper::COG,
            $this->getSelectLink(),
            [self::_TITLE => 'Crud select database table']
        );
        $linkNew = glyphHelper::getLinked(
            glyphHelper::PLUS_SIGN,
            $this->getEditLink(),
            [self::_TITLE => 'Crud add new record']
        );
        return $this->getWidgetLinkWrapper($linkNew . $linkSelect);
    }

    /**
     * getSelectLink
     *
     * @return string
     */
    protected function getSelectLink()
    {
        $route = [
            $this->baseUrl,
            'crud',
            'index',
            self::_SLOT,
            $this->slot,
            self::_TABLE,
            $this->table
        ];
        return implode('/', $route);
    }

    /**
     * getEditLink
     *
     * @return string
     */
    protected function getEditLink()
    {
        return implode('/', [$this->baseUrl, 'crud', 'edit']);
    }

    /**
     * getSearchLink
     *
     * @return string
     */
    protected function getSearchLink()
    {
        return implode('/', [$this->baseUrl, 'crud', 'manage']);
    }

    /**
     * removeSchemaFromName
     *
     * @param string $tablename
     * @return string
     */
    protected function removeSchemaFromName($tablename)
    {
        $parts = explode('.', $tablename);
        return (count($parts) > 1) ? $parts[1] : $tablename;
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
        $filter = [
            '(acl.*)\/(.*)(ge)$',
            '(user.*)\/(.*)(it)$',
            //'(database.*)\/((?!async)csv)$',
            '(database.*)\/((im)|(up))',
            '(database.*)\/(.*)(ex|ge|il|it|rd|er)$',
            '(database.*)\/(tables(?!4d))',
        ];
        return (new autoNavConfig)->setFilter($filter)->render()->getConfig();
    }

    /**
     * getSelectAssist
     *
     * @return array
     */
    protected function getSelectAssist()
    {
        return sessionAssistTools::getSearch(
            self::ASSIST_CRUD_SELECT,
            $this->getApp()->getRequest(),
            $this->getParams(self::_RESET)
        );
    }

    /**
     * getSearchAssist
     *
     * @return array
     */
    protected function getSearchAssist()
    {
        return sessionAssistTools::getSearch(
            self::ASSIST_CRUD_SEARCH,
            $this->getApp()->getRequest(),
            $this->getParams(self::_RESET)
        );
    }

    /**
     * getListToolbar
     *
     * @return array
     */
    private function getListToolbar()
    {
        return [
            glyphToolbar::EXCLUDE_DETAIL => false
            , glyphToolbar::EXCLUDE_IMPORT => true
            , glyphToolbar::EXCLUDE_NEWSLETTER => true
            , glyphToolbar::EXCLUDE_PDF => true
            , glyphToolbar::EXCLUDE_CLONE => false
            , glyphToolbar::EXCLUDE_PEOPLE => true
            , glyphToolbar::EXCLUDE_REFUSE => true
            , glyphToolbar::EXCLUDE_VALIDATE => true
        ];
    }

    /**
     * fieldFactory
     *
     * @return array
     */
    private function fieldFactory()
    {
        $columnName = '';
        $columnPrimary = '';
        $columnPrimaryValue = '';
        $descName = '';
        switch ($this->adapter) {
            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_MYSQL:
                $columnName = 'column_name';
                $columnPrimary = 'key_name';
                $columnPrimaryValue = 'PRIMARY';
                $descName = 'field';
                break;

            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_SQLITE:
                $columnName = 'name';
                $columnPrimary = 'primary';
                $columnPrimaryValue = true;
                $descName = $columnName;
                break;

            case \Pimvc\Db\Model\Core::MODEL_ADAPTER_PGSQL:
                $columnName = 'attname';
                $columnPrimary = 'indisprimary';
                $columnPrimaryValue = true;
                $descName = 'column_name';
                break;
        }
        return [$columnName, $columnPrimary, $columnPrimaryValue, $descName];
    }

    /**
     * initAssets
     *
     */
    private function initAssets()
    {
        $cssPath = '/public/css/';
        $cssAssets = [
            'widget.css', 'tables/table-6.css', 'jquery.selectbox.css',
            'chosen.css', 'form_responsive.css', 'main.css', 'spinkit/cube-grid.css'
        ];
        $cssAssetsCount = count($cssAssets);
        for ($c = 0; $c < $cssAssetsCount; $c++) {
            cssCollection::add($cssPath . $cssAssets[$c]);
        }
        cssCollection::save();
        $jsPath = '/public/js/';
        $jsAssets = [
            'sortable.js', 'chosen.jquery.js', 'jquery.autogrow.js',
            'jquery.columnmanager.js', 'jquery.cookie.js'
        ];
        $jsAssetsCount = count($jsAssets);
        for ($c = 0; $c < $jsAssetsCount; $c++) {
            jsCollection::add($jsPath . $jsAssets[$c]);
        }
        unset($cssAssets);
        unset($jsAssets);
        jsCollection::save();
    }

    /**
     * setTableExists
     * @return $this
     */
    private function setTableExists()
    {
        $forge = new \Pimvc\Db\Model\Forge($this->slot);
        try {
            $this->tableExists = $forge->tableExist(
                $this->removeSchemaFromName($this->table)
            );
        } catch (\Exception $e) {
            $this->tableExists = false;
        };
        return $this;
    }
}
