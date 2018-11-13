<?php
/**
 * App1\Controller\Database
 *
 * is a controller for database table description and code generation.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */
namespace App1\Controller;

use \App1\Views\Helpers\Bootstrap\Tab as bootstrapTab;
use App1\Helper\Controller\Database as databaseHelperController;
use App1\Form\Files\Upload as uploadForm;
//use \Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Views\Helpers\Fa as faHelper;
use Pimvc\Tools\Flash as flashTools;
use Pimvc\Db\Model\Forge as dbForge;
use Pimvc\Db\Model\Field as dbField;
use Pimvc\Db\Model\Fields as dbFields;
use \Pimvc\Helper\Db\Field\Name\Normalize as FieldNormalizer;
use App1\Form\Database\Import as formImport;

class Database extends databaseHelperController
{

    const DOCUMENT_MIME_CSV = 'text/csv';
    const DOCUMENT_UPLOAD_EXTRA = 'Le fichier doit porter les extensions suivantes ';
    const FORM_FILE_MAX_FILESIZE = 52428800;
    const DOCUMENT_PATH = 'cache/documents/';
    const UPLOAD_SUCCESS = 'Upload successful for ';

    /**
     * tables4d
     *
     * @return array
     */
    final public function tables4d()
    {
        $content = \App1\Views\Helpers\Urlselector::get(
                self::PARAM_TABLES_4D, $this->baseUrl . 'database/tables4d/id/', $this->tableList, $this->getParams(self::_ID)
        );

        if ($this->hasValue(self::_ID)) {
            $tableId = $this->getParams(self::_ID);
            $flipedList = array_flip($this->tableList);
            $tableName = strtolower($flipedList[$tableId]);
            $link = $this->baseUrl . 'database/gencode4d/id/'
                . $this->getParams(self::_ID);
            $button = $this->getButton(
                    self::LABEL_GENERATE_CODE, $link
                ) . $this->getButton(
                    'Liste', $this->baseUrl . self::LIST_ACTION . '/model/' . $tableName . 's'
            );

            $content .= $button;

            if ($this->indexes) {
                $helper = new \Pimvc\Views\Helpers\Table(
                    'Indexes ' . $this->currentTableName, array(self::_ID, 'Nom', self::PARAM_TYPE, 'Unicité'), $this->indexes
                );
                $helper->setId('indexesColumns-table');
                $helper->setClass('table-6 managetable');
                $helper->render();
                $tabParams['Indexes'] = (string) $helper;
            }

            if ($this->relations) {
                $helper = new \Pimvc\Views\Helpers\Table(
                    'Relations ' . $this->currentTableName, array('Pk', 'Table', 'Fk', 'Cascade'), $this->relations
                );
                $helper->setId('colonnes-relations');
                $helper->setClass('table-6 managetable');
                $helper->render();
                $tabParams['Relations'] = (string) $helper;
            }

            // Colonnes
            $helper = new \Pimvc\Views\Helpers\Table(
                'Colonnes ' . $this->currentTableName, array(self::_ID, 'Nom', 'Type 4d', 'Type Pdo', 'Longeur'), $this->columns
            );

            $helper->setId('colonnes-table');
            $helper->setClass('table-6 managetable');
            $helper->render();
            $tabParams['Columns'] = (string) $helper;


            $tabs = new bootstrapTab($tabParams);
            $tabs->setSelected('Indexes')
                ->setPaneClass($tabs::TAB_ITEM_CLASS . ' col-sm-12')
                ->setSelected('Indexes')
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
     * tablespgsql
     *
     * @return array
     */
    final public function tablespgsql()
    {
        $content = '<h1><span class="fa fa-database"></span>&nbsp;Tables Pgsql</h1>';
        $targetUrl = $this->baseUrl . '/database/tablespgsql/id/';
        $content .= \App1\Views\Helpers\Urlselector::get(
                'tables-pgsql', $targetUrl, $this->tableList, $this->getParams(self::_ID)
        );
        $viewParams = [
            'nav' => (string) $this->getNav()
            , 'content' => (string) $content
        ];
        $view = $this->getView($viewParams, '/Views/Database/Tablesmysql.php');
        return (string) $this->getLayout($view);
    }

    /**
     * tablesmysql
     *
     * @return string
     */
    final public function tablesmysql()
    {
        $content = '<h1><span class="fa fa-database"></span>&nbsp;Tables Mysql</h1>'
            . \App1\Views\Helpers\Urlselector::get(
                self::PARAM_TABLES_4D, $this->baseUrl . '/database/tablesmysql/id/', $this->tableList, $this->getParams(self::_ID)
        );

        if ($this->hasValue(self::_ID)) {
            $tabParams = array();
            $link = $this->baseUrl . '/database/gencodemysql/id/'
                . $this->getParams(self::_ID);
            $button = $this->getButton(
                self::LABEL_GENERATE_CODE . ' Mysql generation', $link
            );

            $helper = new \Pimvc\Views\Helpers\Table();
            $classTable = 'table-6 managetable';
            // Indexes
            $helper->setTitle('Indexes ' . $this->currentTableName)
                ->setHeader([self::_ID, 'Nom', self::PARAM_TYPE, 'Unicité'])
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
        $modelSuffix = ($this->hasValue('suffix')) ? $this->getParams('suffix') : 'Proscope_';
        $content = \App1\Views\Helpers\Urlselector::get(
                self::PARAM_TABLES_4D, $this->baseUrl . 'database/gencode4d/id/', $this->tableList, $this->getParams(self::_ID)
        );
        if ($this->hasValue(self::_ID)) {
            $link = $this->baseUrl . 'database/tables4d/id/'
                . $this->getParams(self::_ID);
            $button = $this->getButton('Modèle', $link);
            $content .= $button;
            $tabParams = array();
            $tableList = array_flip($this->tableList);
            $tabParams['Domain'] = \Pimvc\Db\Generate\Domain::get(
                    $tableList[$this->getParams(self::_ID)], $this->columns, $this->indexes, $this->relations
            );

            $tableList = array_flip($this->tableList);
            $tabParams['Model'] = \Pimvc\Db\Generate\Model::get(
                    self::PDO_ADPATER_4D, $tableList[$this->getParams(self::_ID)], $this->indexes, $this->relations
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
                self::PARAM_TABLES_4D, $this->baseUrl . '/database/gencodemysql/id/', $this->tableList, $this->getParams(self::_ID)
        );
        if ($this->hasValue(self::_ID)) {
            $tabsParams = [];
            $link = $this->baseUrl . '/database/tablesmysql/id/'
                . $this->currentTableName;
            $button = $this->getButton('Back to Mysl model', $link);
            $tabsParams['Domain'] = \Pimvc\Db\Generate\Domain::get(
                    $this->currentTableName, $this->columns, $this->indexes, $this->relations
            );
            $tabsParams['Model'] = \Pimvc\Db\Generate\Model::get(
                    self::PDO_ADPATER_MYSQL, $this->currentTableName, $this->indexes, $this->relations
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
     * uploadcsv
     *
     * @return array
     */
    final public function uploadcsv()
    {
        $formAction = $this->baseUrl . '/database/uploadcsv';
        $docPath = $this->getDocumentPath();
        $form = new uploadForm(
            $this->getParams(), $formAction, self::FORM_FILE_MAX_FILESIZE
        );
        $form->_setDestination($docPath)
            ->_setMaxsize(self::FORM_FILE_MAX_FILESIZE)
            ->_setAllowedType([self::DOCUMENT_MIME_CSV])
            ->_setAllowedExtension(['.csv', '.Csv', '.CSV'])
            ->render();
        if ($this->isPost()) {
            $filesInfos = $form->_getUploadInfos();
            if ($form->isValid()) {
                $returnCode = $form->_move();
                $parser = new \Pimvc\File\Csv\Parser();
                $fullPathName = $docPath . $filesInfos->filename;
                $delimiter = $parser->auto($fullPathName);
                if ($parser->error != 0) {
                    flashTools::addError($parser->error_info);
                } else {
                    $message = self::UPLOAD_SUCCESS . $filesInfos->filename;
                    flashTools::addInfo($message);
                }
            }
        }
        $csvIcon = faHelper::get(faHelper::FILE);
        $widgetTitle = $csvIcon . ' Csv upload';
        $viewParams = [
            'nav' => (string) $this->getNav()
            , 'content' => $this->getWidget($widgetTitle, (string) $form)
        ];
        $view = $this->getView($viewParams, '/Views/Database/Gencodemysql.php');
        return (string) $this->getLayout($view);
    }

    /**
     * importcsv
     *
     * @return string
     */
    final public function importcsv()
    {
        $form = new formImport($this->getParams());
        $isPost = $this->isPost();
        $isValid = $form->isValid();
        $pagesize = $this->getParams('poolsize');
        if ($isPost && $isValid) {
            $form->setMode('readonly');
            $parser = new \Pimvc\File\Csv\Parser();
            $filepath = $this->getDocumentPath() . $this->getParams('filename');
            $delimiter = $parser->auto($filepath);

            if ($parser->error != 0) {
                flashTools::addError('Error occured during csv parsing');
            } else {
                $headers = array_keys($parser->data[0]);
                $normalizedHeaders = FieldNormalizer::normalizeFieldsName(
                        $headers
                );
                $mapperHeader = array_combine($headers, $normalizedHeaders);
                $fields = new dbFields();
                foreach ($headers as $columnName) {
                    $_field = (new dbField())
                        ->setFromData($parser->data, $columnName)
                        ->setName($mapperHeader[$columnName]);
                    $fields->addItem($_field);
                }
                $forge = new dbForge($this->getParams('slot'));
                $forge->tableCreate(
                    $this->getParams('tablename'), $fields, true
                );
                sleep(1);
                unset($headers);
                unset($fields);
                unset($mapperHeader);
                unset($parser);
                unset($forge);
            }
        } elseif (!$isValid && $isPost) {
            foreach ($form->getErrors() as $fieldName => $error) {
                flashTools::addWarning($fieldName . ' ' . $error);
            }
        }
        $csvIcon = faHelper::get(faHelper::FILE_TEXT);
        $widgetTitle = $csvIcon . ' Csv import - Table creation';
        $widgetContent = (string) $form;
        $viewParams = [
            'isValid' => $isValid,
            'tablename' => $this->getParams('tablename'),
            'filename' => $this->getParams('filename'),
            'slot' => $this->getParams('slot'),
            'pagesize' => ($pagesize) ? $pagesize : 100,
            'page' => 1,
            'ingest' => $isPost,
            'nav' => (string) $this->getNav()
            , 'content' => $this->getWidget($widgetTitle, $widgetContent, 'widget-body')
        ];
        $view = $this->getView($viewParams, '/Views/Database/Importcsv.php');
        return (string) $this->getLayout($view);
    }

    /**
     * asyncimportcsv
     */
    final public function asyncimportcsv()
    {
        $pagesize = $this->getParams('pagesize');
        $page = $this->getParams('page');
        $slot = $this->getParams('slot');
        $tablename = $this->getParams('tablename');
        $filename = $this->getParams('filename');
        $isValid = $pagesize && $page && $slot && $tablename && $filename;
        $response = new \stdClass();
        $response->error = 0;
        $response->jsonError = JSON_ERROR_NONE;
        $response->errors = [];
        $response->request = new \stdClass;
        $response->request = (object) $this->getParams();
        if ($isValid) {
            $forge = new dbForge($slot);
            $filepath = $this->getDocumentPath() . $filename;
            $tableExists = $forge->tableExist($tablename);
            if ($tableExists) {
                $fileExist = file_exists($filepath);
                if ($fileExist) {
                    $response->headers = $this->getNormalizedHeaders($filepath);
                    $parser = new \Pimvc\File\Csv\Parser();
                    $parser->fields = $response->headers;
                    $parser->offset = $response->offset = (($page - 1) * $pagesize);
                    $parser->limit = $response->limit = $pagesize;
                    $response->maxline = $this->countFileLines($filepath);
                    $percent = ($parser->offset * 100) / $response->maxline;
                    $response->progress = round($percent, 0);
                    $delimiter = $parser->auto($filepath);
                    $response->datas = [];
                    $response->linesError = [];
                    $response->delim = $delimiter;
                    $dc = count($parser->data);
                    for ($c = 0; $c < $dc; $c++) {
                        $response->datas[] = $parser->data[$c];
                        $resok = $forge->tableInsert(
                            $tablename, $response->headers, $parser->data[$c]
                        );
                        if (!$resok) {
                            $response->linesError[] = $parser->offset + $c;
                        }
                    }
                    unset($parser);
                } else {
                    $response->error = 1;
                    $response->errors[] = 'incorrect filename';
                }
            } else {
                $response->error = 1;
                $response->errors[] = 'incorrect tablename';
            }
            unset($forge);
        } else {
            $response->error = 1;
            $response->errors[] = 'missing params';
        }
        return $this->getJsonResponse($response);
    }

    /**
     * getNormalizedHeaders
     *
     * @param string $filepath
     * @return array
     */
    private function getNormalizedHeaders(string $filepath): array
    {
        $parser = new \Pimvc\File\Csv\Parser();
        $parser->offset = 0;
        $parser->limit = 1;
        $delimiter = $parser->auto($filepath);
        $headers = array_keys($parser->data[0]);
        unset($parser);
        return FieldNormalizer::normalizeFieldsName($headers);
    }

    /**
     * getPath
     *
     * @return string
     */
    private function getDocumentPath()
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
    private function countFileLines($filename): int
    {
        $fh = new \SplFileObject($filename, 'r');
        $fh->seek(PHP_INT_MAX);
        $nbLine = $fh->key();
        unset($fh);
        return (int) $nbLine;
    }

    /**
     * isPost
     *
     * @return boolean
     */
    private function isPost()
    {
        return ($this->getApp()->getRequest()->getMethod() === 'POST');
    }
}
