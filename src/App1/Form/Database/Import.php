<?php
/**
 * App1\Form\Database\Import
 *
 * @author pierrefromager
 */
namespace App1\Form\Database;

use Pimvc\Form;
use Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Tools\Session as sessionTool;

class Import extends Form
{

    const DB_IMPORT_ACTION = 'database/importcsv';
    const DB_IMPORT_FORM_NAME = 'database-import-csv';
    const DB_IMPORT_FORM_METHOD = 'post';
    const DB_IMPORT_DECORATOR_BREAK = '<br style="clear:both"/>';
    const DOCUMENT_PATH = 'cache/documents/';
    const _DB_POOL = 'dbPool';
    const _FILENAME = 'filename';
    const _SLOT = 'slot';
    const _TABLENAME = 'tablename';
    const _POOL_SIZE = 'poolsize';

    protected $isAdmin;
    protected $postedData;
    protected $app;
    protected $baseUrl;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return App1\Form\Database\Import
     */
    public function __construct($postedDatas)
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->isAdmin = sessionTool::isAdmin();
        $this->postedData = $postedDatas;
        if (empty($postedDatas[self::_POOL_SIZE])) {
            $this->postedData[self::_POOL_SIZE] = 100;
        }
        parent::__construct(
            $this->_getFields(),
            self::DB_IMPORT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::DB_IMPORT_ACTION,
            self::DB_IMPORT_FORM_METHOD,
            $this->postedData
        );

        if ($this->isPost) {
            $this->setMode('disabled');
        }

        $this->setType(self::_FILENAME, 'select');
        $this->setData(self::_FILENAME, $this->getFileList());
        $this->setType(self::_SLOT, 'select');
        $this->setData(self::_SLOT, $this->getSlotList());
        $this->setType(self::_POOL_SIZE, 'select');
        $this->setData(self::_POOL_SIZE, $this->getPoolSize());

        $this->_setWrappers();
        $this->setLabels($this->_getLabels());
        $this->setAlign('left');
        $this->Setsectionsize(40);

        $this->setValues($this->postedData);
        $this->setValidLabelButton('Importer');
        $this->setValidators($this->_getValidators());
        $this->render();
        return $this;
    }

    /**
     * isValid
     *
     * @return type
     */
    public function isValid()
    {
        return parent::isValid();
    }

    /**
     * getDocumentPath
     *
     * @return string
     */
    public function getDocumentPath()
    {
        $documentPath = $this->app->getPath() . self::DOCUMENT_PATH;
        if (!file_exists($documentPath)) {
            mkdir($documentPath, 0777, true);
        }
        return $documentPath;
    }

    private function _getValidators()
    {
        return [
            self::_FILENAME => 'isrequired',
            self::_SLOT => 'isrequired',
            self::_POOL_SIZE => 'isrequired',
            self::_TABLENAME => 'isrequired',
        ];
    }

    /**
     * getFileList
     * @return array
     */
    private function getFileList(): array
    {
        $liste = array_map(
            'basename',
            glob(
                $this->getDocumentPath() . '*.{CSV,Csv,csv}',
                GLOB_BRACE
            )
        );
        return \Pimvc\Tools\Arrayproto::getTupple($liste);
    }

    /**
     * getSlotList
     * @return array
     */
    private function getSlotList(): array
    {
        $slots = array_keys($this->app->getConfig()->getSettings(self::_DB_POOL));
        return \Pimvc\Tools\Arrayproto::getTupple($slots);
    }

    /**
     * getPoolSize
     * @return array
     */
    private function getPoolSize(): array
    {
        $poolSizes = [1, 5, 10, 20, 30, 40, 50, 100, 200, 300, 400, 500, 1000];
        return \Pimvc\Tools\Arrayproto::getTupple($poolSizes);
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $elementWrapper = 'form-element-wrapper';
        $cols12 = $elementWrapper . ' col-sm-12';
        $cols6 = $elementWrapper . ' col-sm-6';
        $formControl = 'form form-control';
        $this->setWrapperClass(self::_FILENAME, $cols12);
        $this->setClass(self::_FILENAME, $formControl);
        $this->setWrapperClass(self::_SLOT, $cols6);
        $this->setClass(self::_SLOT, $formControl);
        $this->setWrapperClass(self::_POOL_SIZE, $cols6);
        $this->setClass(self::_POOL_SIZE, $formControl);
        $this->setWrapperClass(self::_TABLENAME, $cols12);
        $this->setClass(self::_TABLENAME, $formControl);
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [self::_FILENAME, self::_SLOT, self::_POOL_SIZE, self::_TABLENAME];
    }

    /**
     * _getLabels
     *
     * @return array
     */
    private function _getLabels()
    {
        return self::_getStaticLabels();
    }

    /**
     * _getStaticLabels
     *
     * @param boolean $withIcon
     * @return array
     */
    public static function _getStaticLabels($withIcon = true)
    {
        $labels = array(
            self::_FILENAME => 'Fichier',
            self::_SLOT => 'Slot',
            self::_POOL_SIZE => 'Nb of inserts',
            self::_TABLENAME => 'Nom table',
        );
        if ($withIcon) {
            foreach ($labels as $key => $value) {
                $labels[$key] = self::_getLabelIcon($key) . $value;
            }
        }
        return $labels;
    }

    /**
     * _getLabelIcon
     *
     * @param string $fieldName
     * @return string
     */
    private static function _getLabelIcon($fieldName)
    {
        $icons = array(
            self::_FILENAME => glyphHelper::get(glyphHelper::FILE),
            self::_SLOT => glyphHelper::get(glyphHelper::PAPERCLIP),
            self::_POOL_SIZE => glyphHelper::get(glyphHelper::STATS),
            self::_TABLENAME => glyphHelper::get(glyphHelper::SAVE),
        );
        return isset($icons[$fieldName]) ? $icons[$fieldName] : '';
    }
}
