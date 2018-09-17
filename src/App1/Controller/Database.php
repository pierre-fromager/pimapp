<?php
/**
 * class DatabaseController
 * is a controller for database table description and code generation.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */
namespace App1\Controller;

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
        $cssPath = '/public/css/';
        cssCollecion::add($cssPath . 'tables/table-6.css');
        cssCollecion::save();
        $jsPath = '/public/js/';
        jsCollecion::add($jsPath . 'sortable.js');
        jsCollecion::save();
        $actionName = $this->getApp()->getController()->getAction();
        $this->setAdapterFromAction($actionName);
        $this->setTableList();
        if ($this->hasValue(self::PARAM_ID)) {
            $id = $this->getParams(self::PARAM_ID);
            $tableListIds = array_flip($this->tableList);
            $this->currentTableName = $tableListIds[$id];
            unset($tableListIds);
            if ($this->adapter == self::ADAPTER_4D) {
                $this->setIndexesType($id);
                $this->setIndexes($id);
                $this->setConscolumns($id);
                $relationWithLink = ($actionName == 'tables4d');
                $this->setRelations($id, $relationWithLink);
                $this->setColumns($id, !$relationWithLink);
            } elseif ($this->adapter == self::ADAPTER_MYSQL) {
                $this->currentTableName = $this->getParams(self::PARAM_ID);
                $this->setIndexesMysql($id);
                $relationWithLink = ($actionName == 'tablesmysql');
                $this->setRelationsMysql($id, $relationWithLink);
                $this->setColumnsMysql($id, !$relationWithLink);
            } elseif ($this->adapter == self::ADAPTER_PGSQL) {
                $this->currentTableName = $this->getParam(self::PARAM_ID);
                $this->setIndexesPgsql($id);
                $relationWithLink = ($actionName == 'tablespgsql');
                $this->setColumnsMysql($id, !$relationWithLink);
            }
        }
    }

    /**
     * setAdapterFromAction
     *
     * @param string $action
     */
    private function setAdapterFromAction($action)
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
    private function setTableList()
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
                $tablesModel = new Model_4d_Tables();
                $this->tableList = $tablesModel->getPair();
                break;
            case self::ADAPTER_PGSQL:
                $tablesModel = new Model_Pgsql_Tables();
                $this->tableList = $tablesModel->getTables();
                break;
        }
        unset($tablesModel);
    }

    /**
     * setConscolumns
     *
     * @param int $id
     * @return array
     */
    private function setConscolumns($id)
    {
        $results = array();
        $indColumnModel = new Model_4d_Conscolumns();
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
    private function getConscolumn($contrainName)
    {
        return (isset($this->consColumns[$contrainName])) ? $this->consColumns[$contrainName] : '';
    }

    /**
     * setIndexes
     *
     * @param int $tableId
     */
    private function setIndexes($tableId)
    {
        $indColumnModel = new Model_4d_Indcolumns();
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
     * setIndexesType
     *
     * @param int $tableId
     */
    private function setIndexesType($tableId)
    {
        $indexes = array();
        $indexModel = new Model_4d_Indexes();
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
    private function setIndexesMysql($tableId)
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
    private function getIndexType($indexId)
    {
        return $this->indexesType[$indexId];
    }

    /**
     * setRelations
     *
     * @param int $tableId
     * @param boolean $withLink
     */
    private function setRelations($tableId, $withLink = false)
    {
        $constraintsModel = new Model_4d_Constraints();
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
    private function setRelationsMysql($tableId, $withLink = false)
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
     * setColumns
     *
     * @param int $tableId
     * @param boolean $withKey
     */
    private function setColumns($tableId, $withKey = false)
    {
        // Colonnes
        $modelOptions = array('useCache' => false);
        $columsModel = new Model_4d_Columns($modelOptions);
        $resultModel = $columsModel->getByTableId($tableId);
        $columnsData = array();
        unset($columsModel);
        $columnsList = array();
        foreach ($resultModel as $column) {
            $type4d = $column[self::PARAM_DATA_TYPE];
            $type4dLabel = Tools_Db_4d_Types::getLabel($type4d);
            $typePdo = Tools_Db_4d_Types::getPdo($type4d);
            $pdoLabel = Tools_Db_4d_Types::getPdoLabel($typePdo);
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
    private function setColumnsMysql($tableId, $withKey = false)
    {
        // Colonnes
        $columnsList = array();
        $columnsData = array();
        //$indexModel = new Model_Users();
        $indexModel = new \App1\Model\Users($this->modelConfig);

        $cols = $indexModel->describeTable($tableId);
        $c = -1;
        foreach ($cols as $column) {
            $c++;
            $type = trim(preg_replace("/\([^)]+\)/", "", $column[self::PARAM_TYPE]));
            preg_match('#\((.*?)\)#', $column[self::PARAM_TYPE], $match);
            $length = !empty($match[1]) ? $match[1] : 12;
            $typePdo = (preg_match('/int/', $type)) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
            //$pdoLabel = Tools_Db_Pdo_Types::getPdoLabel($typePdo);
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
    private function setColumnsPgsql($tableId, $withKey = false)
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
            $pdoLabel = Tools_Db_Pdo_Types::getPdoLabel($typePdo);
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
    private function setIndexesPgsql($tableId)
    {
        $indexes = array();
        $pgsqlSchema = new Model_Pgsql_Tables();
        $resultModel = $pgsqlSchema->_getColumns($tableId);
        $infosFields = $pgsqlSchema->_getInfoFields($tableId);
        $infosFields = Tools_Array::array_column($infosFields, null, 'column');
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
     * tables4dAction
     *
     * @return array
     */
    public function tables4dAction()
    {
        $content = \App1\Views\Helpers\Urlselector::get(
            self::PARAM_TABLES_4D,
            $this->baseUrl . 'database/tables4d/id/',
            $this->tableList,
            $this->getParam(self::PARAM_ID)
        );

        if ($this->hasValue(self::PARAM_ID)) {
            $tableId = $this->getParam(self::PARAM_ID);
            $flipedList = array_flip($this->tableList);
            $tableName = strtolower($flipedList[$tableId]);
            $link = $this->baseUrl . 'database/gencode4d/id/'
                . $this->getParam(self::PARAM_ID);
            $button = $this->getButton(
                self::LABEL_GENERATE_CODE,
                $link,
                self::PARAM_BUTTON
            ) . $this->getButton(
                'Liste',
                $this->baseUrl . self::LIST_ACTION . '/model/' . $tableName . 's',
                self::PARAM_BUTTON
            );

            $content .= $button;

            // Indexes
            if ($this->indexes) {
                $helper = new \Pimvc\Views\Helpers\Table(
                    'Indexes ' . $this->currentTableName,
                    array(self::PARAM_ID, 'Nom', self::PARAM_TYPE, 'Unicité'),
                    $this->indexes
                );
                $helper->setId('indexesColumns-table');
                $helper->setClass('table-6 managetable');
                $helper->render();
                $tabParams['Indexes'] = (string) $helper;
            }

            // Relations
            if ($this->relations) {
                $helper = new \Pimvc\Views\Helpers\Table(
                    'Relations ' . $this->currentTableName,
                    array('Pk', 'Table', 'Fk', 'Cascade'),
                    $this->relations
                );
                $helper->setId('colonnes-relations');
                $helper->setClass('table-6 managetable');
                $helper->render();
                $tabParams['Relations'] = (string) $helper;
            }

            // Colonnes
            $helper = new \Pimvc\Views\Helpers\Table(
                'Colonnes ' . $this->currentTableName,
                array(self::PARAM_ID, 'Nom', 'Type 4d', 'Type Pdo', 'Longeur'),
                $this->columns
            );

            $helper->setId('colonnes-table');
            $helper->setClass('table-6 managetable');
            $helper->render();
            $tabParams['Columns'] = (string) $helper;

            $tab = new Helper_Bootstrap_Tab($tabParams);
            $tab->setId('tabs-database-4d');
            $tab->setClass('nav nav-tabs');
            $paneClass = Helper_Bootstrap_Tab::TAB_ITEM_CLASS . ' col-sm-12';
            $tab->setPaneClass($paneClass);
            $tab->setSelected('Indexes');
            $tab->render();
            $content .= (string) $tab;
        }

        return array('content' => $content);
    }

    /**
     * tablespgsqlAction
     *
     * @return array
     */
    public function tablespgsqlAction()
    {
        $content = \App1\Views\Helpers\Urlselector::get(
            self::PARAM_TABLES_4D,
            $this->baseUrl . 'database/tablespgsql/id/',
            $this->tableList,
            $this->getParam(self::PARAM_ID)
        );
        return array('content' => $content);
    }

    /**
     * tablesmysqlAction
     *
     * @return array
     */
    public function tablesmysql()
    {
        $content = '<h1><span class="fa fa-database"></span>&nbsp;Table Mysql</h1>'
            . \App1\Views\Helpers\Urlselector::get(
                self::PARAM_TABLES_4D,
                $this->baseUrl . '/database/tablesmysql/id/',
                $this->tableList,
                $this->getParams(self::PARAM_ID)
            );

        if ($this->hasValue(self::PARAM_ID)) {
            $tabParams = array();
            $link = $this->baseUrl . '/database/gencodemysql/id/'
                . $this->getParams(self::PARAM_ID);
            $button = $this->getButton(
                self::LABEL_GENERATE_CODE . ' Mysql generation',
                $link,
                self::PARAM_BUTTON,
                '<br style="clear:both"/>'
            );

            $helper = new \Pimvc\Views\Helpers\Table();
            $classTable = 'table-6 managetable';
            // Indexes
            $helper->setTitle('Indexes ' . $this->currentTableName)
                ->setHeader([self::PARAM_ID, 'Nom', self::PARAM_TYPE, 'Unicité'])
                ->setData($this->indexes)
                ->setId('indexesColumns-table')
                ->setClass($classTable)
                ->render();
            $tabParams['indexes'] = (string) $helper;

            // Relations
            $helper->setTitle('Relations ' . $this->currentTableName)
                ->setHeader(['Pk', 'Table', 'Fk', 'Cascade'])
                ->setData($this->relations)
                ->setId('colonnes-relations')
                ->setClass($classTable)
                ->render();
            $tabParams['relations'] = (string) $helper;

            // Colonnes
            $helper->setTitle('Colonnes ' . $this->currentTableName)
                ->setHeader(['Nom', 'Type Pdo', 'Longeur'])
                ->setData($this->columns)
                ->setId('colonnes-table')
                ->setClass($classTable)
                ->render();
            $tabParams['Colonnes'] = (string) $helper;

            $tabs = new bootstrapTab($tabParams);
            $tabs->setSelected('indexes')
                ->setPaneClass($tabs::TAB_ITEM_CLASS . ' col-sm-12')
                ->render();
            $content .= $button . (string) $tabs;
        }

        $viewParams = [
            'nav' => (string) $this->getNav()
            , 'content' => (string) $content
        ];
        $view = $this->getView($viewParams, '/Views/Database/Tablesmysql.php');
        return (string) $this->getLayout($view);
    }

    /**
     * gencode4dAction
     *
     * @return array
     */
    public function gencode4dAction()
    {
        $modelSuffix = ($this->hasValue('suffix')) ? $this->getParam('suffix') : 'Proscope_';
        $content = \App1\Views\Helpers\Urlselector::get(
            self::PARAM_TABLES_4D,
            $this->baseUrl . 'database/gencode4d/id/',
            $this->tableList,
            $this->getParam(self::PARAM_ID)
        );
        if ($this->hasValue(self::PARAM_ID)) {
            $link = $this->baseUrl . 'database/tables4d/id/'
                . $this->getParam(self::PARAM_ID);
            $button = $this->getButton(
                'Modèle',
                $link,
                self::PARAM_BUTTON,
                '<br style="clear:both"/>'
            );
            $content .= $button;
            $tabParams = array();
            $tableList = array_flip($this->tableList);
            $tabParams['Mapper'] = Tools_Db_Generate_Mapper::get(
                $tableList[$this->getParam(self::PARAM_ID)],
                $this->columns,
                $this->indexes,
                $this->relations,
                $modelSuffix
            );

            $tableList = array_flip($this->tableList);
            $tabParams['Model'] = Tools_Db_Generate_Model::get(
                self::PDO_ADPATER_4D,
                $tableList[$this->getParam(self::PARAM_ID)],
                $this->indexes,
                $this->relations,
                $modelSuffix
            );
            $helperTab = new Helper_Tab($tabParams);
            $helperTab->render();
            $content .= (string) $helperTab;
        }

        return array('content' => $content);
    }

    /**
     * proscopecoverageAction
     *
     */
    public function proscopecoverageAction()
    {
        $cacheName = 'dico4d.tmp';
        $cachePath = APP_PATH . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        $cacheFilename = $cachePath . $cacheName;
        if ($this->hasValue('force')) {
            $modelOptions = array('useCache' => false);
            $model4dTable = new Model_4d_Tables($modelOptions);
            $tables = $model4dTable->getPair();
            $resultColumns = array();
            foreach ($tables as $tableName => $tableId) {
                $columsModel = new Model_4d_Columns($modelOptions);
                $resultColumnsGlobal = $columsModel->getByTableId($tableId);
                $fields = array_map(
                    'strtolower',
                    Tools_Array::array_column(
                        $resultColumnsGlobal,
                        'column_name'
                    )
                );
                $className = self::MODEL_DOMAIN_PREFIX
                    . ucfirst(strtolower(str_replace('_', '', $tableName))) . 's';
                $resultColumns[$className] = array_flip($fields);
            }
            file_put_contents($cacheFilename, serialize($resultColumns));
            unset($tables);
            unset($model4dTable);
        } else {
            $resultColumns = unserialize(file_get_contents($cacheFilename));
        }

        // Check if properties exist
        $countYes = 0;
        $countNo = 0;
        foreach ($resultColumns as $modelClass => $fieldList) {
            $fields = array_keys($fieldList);
            foreach ($fields as $field) {
                $resultColumns[$modelClass][$field] = (class_exists($modelClass)) ? (property_exists($modelClass, $field) ? 'Y' : 'N') : 'N';
                if ($resultColumns[$modelClass][$field] == 'Y') {
                    ++$countYes;
                } else {
                    ++$countNo;
                }
            }
        }

        // Report
        $report = '';
        foreach ($resultColumns as $modelClass => $fieldList) {
            $fields = array_keys($fieldList);
            $classExist = (class_exists($modelClass));
            $diff = array();
            if ($classExist) {
                $mapper = new $modelClass();
                $vars = $mapper->getVars();
                $diff = array_diff($vars, $fields);
                unset($mapper);
            }
            $shortModelName = str_replace(self::MODEL_DOMAIN_PREFIX, '', $modelClass);
            $report .= '<div class="modelWrapper row col-xs-12 col-sm-12 col-md-12 col-lg-6">'
                . '<span class="modelname"'
                . ' onclick="$j(\'#' . $modelClass . '\').toggle();"'
                . '>' . $shortModelName . '</span>'
            ;
            $report .= ''
                . '<ul class="toggled clearfix" id="' . $modelClass . '">';
            if ($diff && $classExist) {
                foreach ($diff as $missing) {
                    $report .= '<li class="removed">' . $missing . '</li>';
                }
            }
            foreach ($fields as $field) {
                $fieldExist = ($resultColumns[$modelClass][$field] == 'Y');
                $class = ($fieldExist) ? 'valid' : 'missing';
                $report .= '<li class="' . $class . '">' . $field . '</li>';
            }
            $report .= '</ul>';
            $report .= '</div>';
        }

        $btnClass = 'btn btn-xs ';
        $editClass = $btnClass . 'btn-primary ';
        $refreshUrl = $this->baseUrl . 'database/proscopecoverage';
        $refreshButton = '<a href="' . $refreshUrl . '" class="' . $editClass . '">'
            . Helper_Glyph::get(Helper_Glyph::refresh) . 'Refresh'
            . '</a>';
        $scanClass = $btnClass . 'btn-warning ';
        $scanUrl = $this->baseUrl . 'database/proscopecoverage/force/go';
        $scanButton = '<a href="' . $scanUrl . '" class="' . $scanClass . '">'
            . Helper_Glyph::get(Helper_Glyph::zoom_in) . 'Coverage'
            . '</a>';
        $buttons = '<div class="row">'
            . $refreshButton
            . $scanButton
            . '<a class="' . $btnClass . 'btn-primary " onclick="$j(\'.toggled\').toggle();">Toggle</a>'
            . '</div>';


        $total = $countYes + $countNo;
        $pct = round((100 * $countYes) / $total);
        $content = $buttons
            . '<br/>'
            . '<div style="text:align:left">'
            . '<span class="stat">Total     : ' . $total . '</span>'
            . '<span class="stat">&nbsp;Total valid : ' . $countYes . '</span>'
            . '<span class="stat">&nbsp;Total issue  : ' . $countNo . '</span>'
            . '<span class="stat">&nbsp;Coverage  : ' . $pct . '%</span>'
            . '</div>'
            . $report;
        $partialParams = array('baseUrl' => $this->baseUrl);
        $partial = new Helper_Partial($partialParams, 'Database/Coverage.html');
        $partialContent = (string) $partial . $content . '<br style="clear:both"/>';
        unset($partial);
        unset($resultColumns);
        $widget = new Helper_Widget(
            'Proscope mapper coverage',
            $partialContent
        );
        $widget->render();
        $content = (string) $widget;
        unset($widget);
        return array('content' => $content);
    }

    /**
     * gencodemysqlAction
     *
     * @return array
     */
    public function gencodemysql()
    {
        $content = \App1\Views\Helpers\Urlselector::get(
            self::PARAM_TABLES_4D,
            $this->baseUrl . '/database/gencodemysql/id/',
            $this->tableList,
            $this->getParams(self::PARAM_ID)
        );
        if ($this->hasValue(self::PARAM_ID)) {
            $tabsParams = [];
            $link = $this->baseUrl . '/database/tablesmysql/id/'
                . $this->currentTableName;
            $button = $this->getButton(
                'Back to Mysl model',
                $link,
                self::PARAM_BUTTON,
                '<br style="clear:both"/>'
            );
            $tabsParams['Domain'] = \Pimvc\Db\Generate\Domain::get(
                $this->currentTableName,
                $this->columns,
                $this->indexes,
                $this->relations
            );
            $tabsParams['Model'] = \Pimvc\Db\Generate\Model::get(
                self::PDO_ADPATER_MYSQL,
                $this->currentTableName,
                $this->indexes,
                $this->relations
            );

            $tabs = new bootstrapTab($tabsParams);
            $tabs->setSelected('Domain')
                ->setPaneClass($tabs::TAB_ITEM_CLASS . ' col-sm-12')
                ->render();
            $content .= $button . (string) $tabs;
        }
        $viewParams = [
            'nav' => (string) $this->getNav()
            , 'content' => (string) $content
        ];
        $view = $this->getView($viewParams, '/Views/Database/Gencodemysql.php');
        return (string) $this->getLayout($view);
    }

    /**
     * getButton
     *
     * @param string $label
     * @param string $link
     * @return string
     */
    private function getButton($label, $link)
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
    private function getViewPath($actionName)
    {
        return $this->getApp()->getPath() . self::VIEW_DATABASE_PATH
            . ucfirst($actionName) . '.php';
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    private function getNavConfig()
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
    private function getNav()
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
    private function getLayout($content)
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
