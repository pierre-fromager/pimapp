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
use Pimvc\Views\Helpers\Collection\Css as cssCollecion;
use Pimvc\Views\Helpers\Collection\Js as jsCollecion;
use \App1\Views\Helpers\Bootstrap\Button as bootstrapButton;
use \App1\Views\Helpers\Bootstrap\Tab as bootstrapTab;
use \App1\Model\Users as usersModel;

class Database extends basicController
{

    const MODEL_DOMAIN_PREFIX = 'Model_Domain_Proscope_';
    const PARAM_REQUEST = 'Request';
    const DEFAULT_ADAPTER = 'mysql';
    const ADAPTER_4D = '4d';
    const ADAPTER_PGSQL = 'pgsql';
    const ADAPTER_MYSQL = self::DEFAULT_ADAPTER;
    const PDO_ADPATER_4D = 'Pdo4d';
    const PDO_ADPATER_MYSQL = 'PdoMysql';
    const PARAM_ID = 'id';
    const PARAM_COLUMN_NAME = 'column_name';
    const PARAM_RELATED_COLUMN_NAME = 'related_column_name';
    const PARAM_INDEX_ID = 'index_id';
    const PARAM_INDEX_TYPE = 'index_type';
    const PARAM_COLUMN_ID = 'column_id';
    const PARAM_CONSTRAINT_NAME = 'constraint_name';
    const PARAM_RELATED_TABLE_NAME = 'related_table_name';
    const PARAM_REFRENCED_TABLE_NAME = 'referenced_table_name';
    const PARAM_REFRENCED_COLUMN_NAME = 'referenced_column_name';
    const PARAM_RELATED_TABLE_ID = 'related_table_id';
    const PARAM_UNIQNESS = 'uniqueness';
    const PARAM_4D = '4d';
    const PARAM_MYSQL = 'mysql';
    const PARAM_KEY = 'key';
    const PARAM_EXTRA = 'extra';
    const PARAM_FIELD = 'field';
    const PARAM_TYPE = 'type';
    const PARAM_NAME = 'name';
    const PARAM_YES = 'Oui';
    const PARAM_NO = 'Non';
    const PARAM_LENGTH = 'length';
    const PARAM_DATA_LENGTH = 'data_length';
    const PARAM_DATA_TYPE = 'data_type';
    const PARAM_TABLES_4D = 'tables-4d';
    const LABEL_GENERATE_CODE = 'Code';
    const PARAM_BUTTON = 'button';
    const LIST_ACTION = 'proscope/list';
    const LAYOUT_NAME = 'responsive';
    const PARAM_HTML = 'html';
    const PARAM_NAV = 'nav';
    const VIEW_DATABASE_PATH = 'Views/Database/';

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
        if ($this->hasValue(self::PARAM_ID)) {
            $id = $this->getParams(self::PARAM_ID);
            $tableListIds = array_flip($this->tableList);
            $this->currentTableName = $tableListIds[$id];
            unset($tableListIds);
            if ($this->adapter == self::ADAPTER_4D) {
                $this->init4d($id, $actionName);
            } elseif ($this->adapter == self::ADAPTER_MYSQL) {
                $this->initMysql($id, $actionName);
            } elseif ($this->adapter == self::ADAPTER_PGSQL) {
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
        cssCollecion::add($cssPath . 'tables/table-6.css');
        cssCollecion::save();
        $jsPath = '/public/js/';
        jsCollecion::add($jsPath . 'sortable.js');
        jsCollecion::save();
    }

    /**
     * init4d
     *
     * @param string $id
     * @param string $actionName
     */
    private function init4d($id, $actionName)
    {
        $this->setIndexesType4d($id);
        $this->setIndexes4d($id);
        $this->setConscolumns4d($id);
        $relationWithLink = ($actionName == 'tables4d');
        $this->setRelations4d($id, $relationWithLink);
        $this->setColumns4d($id, !$relationWithLink);
    }

    /**
     * initMysql
     *
     * @param string $id
     * @param string $actionName
     */
    private function initMysql($id, $actionName)
    {
        $this->currentTableName = $this->getParams(self::PARAM_ID);
        $this->setIndexesMysql($id);
        $relationWithLink = ($actionName == 'tablesmysql');
        $this->setRelationsMysql($id, $relationWithLink);
        $this->setColumnsMysql($id, !$relationWithLink);
    }

    /**
     * initPgsql
     *
     * @param string $id
     * @param string $actionName
     */
    private function initPgsql($id, $actionName)
    {
        $this->currentTableName = $this->getParam(self::PARAM_ID);
        $this->setIndexesPgsql($id);
        $relationWithLink = ($actionName == 'tablespgsql');
        $this->setColumnsMysql($id, !$relationWithLink);
    }

    /**
     * setAdapterFromAction
     *
     * @param string $action
     */
    protected function setAdapterFromAction($action)
    {
        if (strpos($action, 'mysql') !== false) {
            $this->adapter = self::ADAPTER_MYSQL;
        } elseif (strpos($action, 'pgsql') !== false) {
            $this->adapter = self::ADAPTER_PGSQL;
        } else {
            //$this->adapter = self::ADAPTER_4D;
            $this->adapter = self::ADAPTER_MYSQL;
        }
    }

    /**
     * setTableList
     *
     */
    protected function setTableList()
    {
        switch ($this->adapter) {
            case self::ADAPTER_MYSQL:
                $tablesModel = new usersModel($this->modelConfig);
                $tables = $tablesModel->showTable();
                foreach ($tables as $key => $value) {
                    $tupples = array_values($value);
                    $this->tableList[] = $tupples[0];
                }
                break;
            case self::ADAPTER_4D:
                $tablesModel = new \Pimvc\Model\Fourd\Tables($this->modelConfig);
                $this->tableList = $tablesModel->getPair();
                break;
            case self::ADAPTER_PGSQL:
                $tablesModel = new \Pimvc\Model\Pgsql\Tables($this->modelConfig);
                $this->tableList = $tablesModel->getTables();
                break;
        }
        unset($tablesModel);
    }

    /**
     * setConscolumns4d
     *
     * @param int $id
     * @return array
     */
    protected function setConscolumns4d($id)
    {
        $results = array();
        $indColumnModel = new \Pimvc\Model\Fourd\Conscolumns($this->modelConfig);
        $resultsCons = $indColumnModel->getByTableId($id);
        foreach ($resultsCons as $result) {
            $contraintName = strtolower($result[self::PARAM_CONSTRAINT_NAME]);
            $results[$contraintName] = array(
                self::PARAM_COLUMN_NAME => strtolower(
                    $result[self::PARAM_COLUMN_NAME]
                )
                , self::PARAM_RELATED_COLUMN_NAME => strtolower(
                    $result[self::PARAM_RELATED_COLUMN_NAME]
                )
            );
        }
        $this->consColumns = $results;
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
     * setIndexes4d
     *
     * @param int $tableId
     */
    protected function setIndexes4d($tableId)
    {
        $indColumnModel = new \Pimvc\Model\Fourd\Indcolumns($this->modelConfig);
        $resultModel = $indColumnModel->getByTableId($tableId);
        unset($indColumnModel);
        $indexesData = array();
        foreach ($resultModel as $index) {
            $type = $this->getIndexType($index[self::PARAM_INDEX_ID]);
            $indexesData[] = array(
                $index[self::PARAM_COLUMN_ID]
                , strtolower($index[self::PARAM_COLUMN_NAME])
                , Tools_Db_4d_Types::getIndexTypeLabel($type[self::PARAM_INDEX_TYPE])
                , ($type[self::PARAM_UNIQNESS] == 1) ? self::PARAM_YES : self::PARAM_NO
            );
        }
        $this->indexes = $indexesData;
        unset($indexesData);
    }

    /**
     * setIndexesType4d
     *
     * @param int $tableId
     */
    protected function setIndexesType4d($tableId)
    {
        $indexes = array();
        $indexModel = new \Pimvc\Model\Fourd\Indexes($this->modelConfig);
        $resultModel = $indexModel->getByTableId($tableId);
        foreach ($resultModel as $index) {
            $indexId = $index[self::PARAM_INDEX_ID];
            $indexes[$indexId] = array(
                self::PARAM_INDEX_TYPE => $index[self::PARAM_INDEX_TYPE]
                , self::PARAM_UNIQNESS => $index[self::PARAM_UNIQNESS]
            );
        }
        $this->indexesType = $indexes;
        unset($indexes);
        unset($resultModel);
        unset($indexModel);
    }

    /**
     * setIndexesMysql
     *
     * @param string $tableId
     */
    protected function setIndexesMysql($tableId)
    {
        $indexes = array();
        $indexModel = new usersModel($this->modelConfig);
        $resultModel = $indexModel->describeTable($tableId);
        $c = -1;
        foreach ($resultModel as $index) {
            $c++;
            $indexId = $index[self::PARAM_KEY];
            $ai = $index[self::PARAM_EXTRA];
            if (!empty($indexId)) {
                $name = $index[self::PARAM_FIELD];
                $indexes[] = array(
                    $c
                    , $name
                    , self::PARAM_INDEX_TYPE => $index[self::PARAM_KEY]
                    , self::PARAM_UNIQNESS => ($ai == 'auto_increment') ? self::PARAM_YES : self::PARAM_NO
                );
            }
        }
        $this->indexes = $indexes;
        unset($indexes);
        unset($resultModel);
        unset($indexModel);
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
     * setRelations4d
     *
     * @param int $tableId
     * @param boolean $withLink
     */
    protected function setRelations4d($tableId, $withLink = false)
    {
        $constraintsModel = new \Pimvc\Model\Fourd\Constraints($this->modelConfig);
        $resultModel = $constraintsModel->getByTableId($tableId);
        $relationData = array();
        $constraintInfo = array();
        unset($constraintsModel);
        foreach ($resultModel as $constraint) {
            $constraintName = strtolower($constraint[self::PARAM_CONSTRAINT_NAME]);
            $constraintInfo = $this->getConscolumn($constraintName);
            $relatedColumn = $constraintInfo[self::PARAM_RELATED_COLUMN_NAME];
            $columnName = $constraintInfo[self::PARAM_COLUMN_NAME];
            $tableLink = '<a class="foreignTableName" href="'
                    . $this->baseUrl . 'database/tables4d/id/'
                    . $constraint[self::PARAM_RELATED_TABLE_ID] . '">'
                    . $constraint[self::PARAM_RELATED_TABLE_NAME] . '</a>';
            $relationData[] = array(
                $columnName
                , ($withLink) ? $tableLink : $constraint[self::PARAM_RELATED_TABLE_NAME]
                , $relatedColumn
                , ($constraint['delete_rule'] == '') ? self::PARAM_NO : self::PARAM_YES
            );
        }
        $this->relations = $relationData;
        unset($constraintInfo);
        unset($relationData);
        unset($resultModel);
    }

    /**
     * setRelationsMysql
     *
     * @param int $tableId
     * @param boolean $withLink
     */
    protected function setRelationsMysql($tableId, $withLink = false)
    {
        $constraintsModel = new \Pimvc\Model\Mysql\Keycolumnusages($this->modelConfig);
        $constraints = $constraintsModel->getByTableName($tableId);
        unset($constraintsModel);
        $relationData = array();
        foreach ($constraints as $constraint) {
            if (isset($constraint[self::PARAM_REFRENCED_TABLE_NAME])) {
                $tableLink = '<a class="foreignTableName" href="'
                        . $this->baseUrl . 'database/tablesmysql/id/'
                        . $constraint[self::PARAM_REFRENCED_TABLE_NAME] . '">'
                        . ucfirst($constraint[self::PARAM_REFRENCED_TABLE_NAME])
                        . '</a>';
                $relatedColumn = $constraint[self::PARAM_REFRENCED_COLUMN_NAME];
                $relationData[] = array(
                    $constraint[self::PARAM_COLUMN_NAME]
                    , ($withLink) ? $tableLink : $constraint[self::PARAM_REFRENCED_TABLE_NAME]
                    , $relatedColumn
                    , self::PARAM_NO //($constraint['delete_rule'] == '') ? self::PARAM_NO : self::PARAM_YES
                );
            }
        }
        $this->relations = $relationData;
        unset($relationData);
        unset($constraints);
    }

    /**
     * setColumns4d
     *
     * @param int $tableId
     * @param boolean $withKey
     */
    protected function setColumns4d($tableId, $withKey = false)
    {
        // Colonnes
        $columsModel = new \Pimvc\Model\Fourd\Columns($this->modelOptions);
        $resultModel = $columsModel->getByTableId($tableId);
        $columnsData = array();
        unset($columsModel);
        $columnsList = array();
        foreach ($resultModel as $column) {
            $type4d = $column[self::PARAM_DATA_TYPE];
            $type4dLabel = \Pimvc\Db\Pdo\Types::getLabel($type4d);
            $typePdo = \Pimvc\Db\Pdo\Types::getPdo($type4d);
            $pdoLabel = \Pimvc\Db\Pdo\Types::getPdoLabel($typePdo);
            $columnsData[] = array(
                $column[self::PARAM_COLUMN_ID]
                , strtolower($column[self::PARAM_COLUMN_NAME])
                , $type4dLabel
                , $pdoLabel
                , $column[self::PARAM_DATA_LENGTH]
            );
            $columnsList[] = array(
                self::PARAM_NAME => $column[self::PARAM_COLUMN_NAME]
                , 't4d' => $type4d
                , self::PARAM_TYPE => $pdoLabel
                , self::PARAM_LENGTH => $column[self::PARAM_DATA_LENGTH]
            );
        }
        unset($resultModel);
        $this->columns = ($withKey) ? $columnsList : $columnsData;
        unset($columnsList);
        unset($columnsData);
    }

    /**
     * setColumnsMysql
     *
     * @param int $tableId
     * @param boolean $withKey
     */
    protected function setColumnsMysql($tableId, $withKey = false)
    {
        // Colonnes
        $columnsList = array();
        $columnsData = array();
        $indexModel = new \App1\Model\Users($this->modelConfig);

        $cols = $indexModel->describeTable($tableId);
        $c = -1;
        foreach ($cols as $column) {
            $c++;
            $type = trim(preg_replace("/\([^)]+\)/", "", $column[self::PARAM_TYPE]));
            preg_match('#\((.*?)\)#', $column[self::PARAM_TYPE], $match);
            $length = !empty($match[1]) ? $match[1] : 12;
            $typePdo = (preg_match('/int/', $type)) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            $pdoLabel = \Pimvc\Db\Pdo\Types::getPdoLabel($typePdo);

            $columnsData[] = array(
                $c
                , $column[self::PARAM_FIELD]
                , $type
                , $pdoLabel
                , $length
            );
            $columnsList[] = array(
                self::PARAM_NAME => $column[self::PARAM_FIELD]
                , self::PARAM_TYPE => $pdoLabel
                , self::PARAM_LENGTH => $length
            );
        }
        unset($indexModel);
        $this->columns = ($withKey) ? $columnsList : $columnsData;
        unset($columnsList);
        unset($columnsData);
    }

    /**
     * setColumnsPgsql
     *
     * @param int $tableId
     * @param boolean $withKey
     */
    protected function setColumnsPgsql($tableId, $withKey = false)
    {
        // Colonnes
        $columnsList = array();
        $columnsData = array();
        $indexModel = new Model_Users();
        $cols = $indexModel->describeTable($tableId);
        $c = -1;
        foreach ($cols as $column) {
            $c++;
            $type = trim(preg_replace("/\([^)]+\)/", "", $column[self::PARAM_TYPE]));
            preg_match('#\((.*?)\)#', $column[self::PARAM_TYPE], $match);
            $length = !empty($match[1]) ? $match[1] : 12;
            $typePdo = (preg_match('/int/', $type)) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $pdoLabel = \Pimvc\Db\Pdo\Types::getPdoLabel($typePdo);
            $columnsData[] = array(
                $c
                , $column[self::PARAM_FIELD]
                , $type
                , $pdoLabel
                , $length
            );
            $columnsList[] = array(
                self::PARAM_NAME => $column[self::PARAM_FIELD]
                , self::PARAM_TYPE => $pdoLabel
                , self::PARAM_LENGTH => $length
            );
        }
        unset($indexModel);
        $this->columns = ($withKey) ? $columnsList : $columnsData;
        unset($columnsList);
        unset($columnsData);
    }

    /**
     * setIndexesMysql
     *
     * @param string $tableId
     */
    protected function setIndexesPgsql($tableId)
    {
        $indexes = array();
        $pgsqlSchema = new \Pimvc\Model\Pgsql\Tables($this->modelConfig);
        $resultModel = $pgsqlSchema->_getColumns($tableId);
        $infosFields = $pgsqlSchema->_getInfoFields($tableId);
        $infosFields = \Pimvc\Tools\Arrayproto::array_column($infosFields, null, 'column');
        var_dump($resultModel, '<hr>', $infosFields);
        die;
        unset($pgsqlSchema);
        $c = -1;
        foreach ($resultModel as $index) {
            $c++;
            $indexId = $index[self::PARAM_KEY];
            $ai = $index[self::PARAM_EXTRA];
            if (!empty($indexId)) {
                $name = $index[self::PARAM_FIELD];
                $indexes[] = array(
                    $c
                    , $name
                    , self::PARAM_INDEX_TYPE => $index[self::PARAM_KEY]
                    , self::PARAM_UNIQNESS => ($ai == 'auto_increment') ? self::PARAM_YES : self::PARAM_NO
                );
            }
        }
        $this->indexes = $indexes;
        unset($indexes);
        unset($resultModel);
        unset($pgsqlSchema);
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
        return [
            'title' => [
                'text' => 'Pimapp',
                'icon' => 'fa fa-home',
                'link' => $this->baseUrl
            ],
            'items' => []
        ];
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
    protected function getLayout($content)
    {
        $layout = (new \App1\Views\Helpers\Layouts\Responsive());
        $layoutParams = ['content' => $content];
        $layout->setApp($this->getApp())
                ->setName(self::LAYOUT_NAME)
                ->setLayoutParams($layoutParams)
                ->build();
        return $layout;
    }
}
