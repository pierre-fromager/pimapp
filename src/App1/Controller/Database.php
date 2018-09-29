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

//use \Pimvc\Controller\Basic as basicController;
//use Pimvc\Views\Helpers\Collection\Css as cssCollecion;
//use Pimvc\Views\Helpers\Collection\Js as jsCollecion;
//use \App1\Views\Helpers\Bootstrap\Button as bootstrapButton;
use \App1\Views\Helpers\Bootstrap\Tab as bootstrapTab;
//use \App1\Model\Users as usersModel;
use App1\Helper\Controller\Database as databaseHelperController;

class Database extends databaseHelperController
{

    protected $baseUrl = '';
    protected $request = null;
    protected $indexes = array();
    protected $indexesType = array();
    protected $columns = array();
    protected $relations = array();
    protected $tableList = array();
    protected $currentTableName = '';
    protected $consColumns = array();
    // private $modelConfig;
    protected $adapter;

    /**
     * tables4dAction
     *
     * @return array
     */
    final public function tables4d()
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
     * tablespgsql
     *
     * @return array
     */
    final public function tablespgsql()
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
    final public function tablesmysql()
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
     * gencode4d
     *
     * @return array
     */
    final public function gencode4d()
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
            $tabParams['Domain'] = \Pimvc\Db\Generate\Domain::get(
                $tableList[$this->getParam(self::PARAM_ID)],
                $this->columns,
                $this->indexes,
                $this->relations,
                $modelSuffix
            );

            $tableList = array_flip($this->tableList);
            $tabParams['Model'] = \Pimvc\Db\Generate\Model::get(
                self::PDO_ADPATER_4D,
                $tableList[$this->getParam(self::PARAM_ID)],
                $this->indexes,
                $this->relations,
                $modelSuffix
            );
            $tabs = new bootstrapTab($tabParams);
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
     * gencodemysql
     *
     * @return string
     */
    final public function gencodemysql()
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
}
