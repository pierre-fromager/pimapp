<?php

/**
 * class App1\Helper\Controller\Database
 *
 * is a controller helper for database table description and code generation.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */

namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \Pimvc\Helper\Model\Mysql as mysqlModelHelper;
use \Pimvc\Helper\Model\Fourd as fourdModelHelper;
use \Pimvc\Helper\Model\Pgsql as pgsqlModelHelper;
use \Pimvc\Helper\Model\IHelper as interfaceModelHelper;
use \Pimvc\Helper\Db\Field\Name\Normalize as FieldNormalizer;
use \App1\Views\Helpers\Bootstrap\Button as bootstrapButton;
use \App1\Helper\Nav\Auto\Config as autoNavConfig;

class Database extends basicController implements interfaceModelHelper
{

    use \App1\Helper\Reuse\Controller;

    const _ID = 'id';
    const PARAM_TABLES_4D = 'tables-4d';
    const LAYOUT_NAME = 'responsive';
    const LABEL_GENERATE_CODE = 'Code';
    const DOCUMENT_MIME_CSV = 'text/csv';
    const DOCUMENT_MIME_QIF = 'application/octet-stream';
    const DOCUMENT_UPLOAD_EXTRA = 'Le fichier doit porter les extensions suivantes ';
    const FORM_FILE_MAX_FILESIZE = 52428800;
    const DOCUMENT_PATH = 'cache/documents/';
    const UPLOAD_SUCCESS = 'Upload successful for ';

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
        $cssAssets = [
            'tables/table-6.css', 'widget.css', 'spinkit/cube-grid.css'
        ];
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
     * qif2csv
     *
     * @param string $filename
     * @return string
     */
    protected function qif2csv(string $filename): string
    {
        $qif = (new \App1\Helper\File\Import\Qif($filename))->parse();
        $csv = new \Pimvc\File\Csv\Parser();
        $csv->delimiter = ';';
        $csv->outputDelimiter = ';';
        $qifData = $qif->asArray();
        $csv->inputEncoding = 'UTF-8';
        $csv->outputEncoding = 'UTF-8';
        $csv->titles = array_keys($qifData[0]);
        $csv->fields = array_keys($qifData[0]);
        $csv->data = $qifData;
        $csvFilename = dirname($filename) . '/' . substr(basename($filename), 0, -3) . 'csv';
        $csv->save($csvFilename);
        return $csvFilename;
    }

    /**
     * postProcessUploadCsv
     *
     * @param string $filename
     */
    protected function postProcessUploadCsv(string $filename)
    {
        $format = substr($filename, -3);
        if ($format === 'qif') {
            $filename = $this->qif2csv($filename);
        }
        $csvContent = preg_replace(
            '/^[ \t]*[\r\n]+/m',
            '',
            file_get_contents($filename)
        );
        file_put_contents($filename, $this->removeUtf8Bom($csvContent));
    }

    /**
     * csvInfo
     *
     * @param string $filename
     * @return array
     */
    protected function csvInfo(string $filename): array
    {
        $filterName = \App1\Helper\Stream\Filter\Csv::MODE_COUNT;
        stream_filter_register(
            \App1\Helper\Stream\Filter\Csv::MODE_ALL,
            \App1\Helper\Stream\Filter\Csv::class
        );
        $filter = 'php://filter/read=' . $filterName . '/resource=file://' . $filename;
        $content = file_get_contents($filter);
        $result = \json_decode($content, true);
        unset($content);
        return (is_null($result)) ? [] : $result;
    }

    /**
     * removeUtf8Bom
     *
     * @param string $text
     * @return string
     */
    private function removeUtf8Bom(string $text): string
    {
        $bom = pack('H*', 'EFBBBF');
        return preg_replace("/^$bom/", '', $text);
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
        $filter = [
            '(acl.*)\/(.*)(ge)$',
            '(user.*)\/(.*)(it)$',
            //'(database.*)\/((?!async)csv)$',
            '(database.*)\/((im)|(up))',
            '(database.*)\/(.*)(ex|ge|il|it|rd|er)$',
            '(database.*)\/(tables(?!4d))',
            '(crud.*)\/(.*)(ge)$'
        ];
        return (new autoNavConfig)->setFilter($filter)->render()->getConfig();
    }

    /**
     * getNormalizedHeaders
     *
     * @param string $filepath
     * @param string $delimiter
     * @return array
     */
    protected function getNormalizedHeaders(string $filepath, string $delimiter): array
    {
        $parser = new \Pimvc\File\Csv\Parser();
        $parser->delimiter = $delimiter;
        $parser->offset = 0;
        $parser->limit = 1;
        $parser->parse($filepath);
        $headers = $parser->titles;
        unset($parser);
        return FieldNormalizer::normalizeFieldsName($headers);
    }

    /**
     * getPath
     *
     * @return string
     */
    protected function getDocumentPath()
    {
        $documentPath = $this->getApp()->getPath() . self::DOCUMENT_PATH;
        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0777, true);
        }
        return $documentPath;
    }

    /**
     * countFileLines
     *
     * @param string $filename
     * @return int
     */
    protected function countFileLines($filename): int
    {
        $fh = new \SplFileObject($filename, 'r');
        $fh->seek(PHP_INT_MAX);
        $nbLine = $fh->key();
        unset($fh);
        return (int) $nbLine;
    }
}
