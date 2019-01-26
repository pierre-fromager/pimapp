<?php

/**
 * App1\Form\Crud\Search
 *
 * @author pierrefromager
 */

namespace App1\Form\Crud;

use Pimvc\Form;
use \Pimvc\Db\Model\Field as modelField;
use \Pimvc\Db\Model\Fields as modelFields;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;

class Search extends Form
{

    const CRUD_SEARCH_ACTION = 'crud/manage';
    const CRUD_SEARCH_FORM_NAME = 'crud-search';
    const CRUD_SEARCH_FORM_METHOD = 'POST';
    const CRUD_SEARCH_DECORATOR_BREAK = '<br style="clear:both"/>';

    protected $isAdmin;
    protected $postedData;
    protected $app;
    protected $baseUrl;
    private $_operations;
    protected $booleanValues = [1, 0];

    /**
     * $adapter
     * @var string
     */
    protected $adapter;

    /**
     * $modelFields
     * @var \Pimvc\Db\Model\Fields
     */
    private $modelFields;

    /**
     * $fieldList
     * @var array
     */
    private $_fieldList;

    /**
     * $_fieldTypesList
     * @var array
     */
    private $_fieldTypesList;

    /**
     * $_fieldIndexes
     * @var array
     */
    private $_fieldIndexes;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @param array $tableFields
     * @return $this
     */
    public function __construct(array $postedDatas, modelFields $fields, $operations)
    {
        $this->modelFields = $fields;
        $this->_fieldList = $this->modelFields->getIndexes(true);
        $this->_fieldTypesList = $this->modelFields->getPdos();
        $this->_fieldIndexes = $this->modelFields->getIndexes(true);
        $this->_operations = $operations;
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->postedData = $postedDatas;

        parent::__construct(
            $this->_fieldList,
            self::CRUD_SEARCH_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::CRUD_SEARCH_ACTION,
            self::CRUD_SEARCH_FORM_METHOD,
            $this->postedData
        );
        $this->_setWrappers();
        $this->setSearchMode('true');
        $this->_setFieldsOptions();
        $this->_setLabels();
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Rechercher');
        $this->render();
        return $this;
    }

    /**
     * _setLabels
     *
     */
    private function _setLabels()
    {
        foreach ($this->_fieldList as $fieldName) {
            $type = $this->_fieldTypesList[$fieldName];
            $isIndex = in_array($fieldName, $this->_fieldIndexes);
            $icons = ($isIndex) ? glyphHelper::get(glyphHelper::INFO_SIGN) : '';
            if ($type === \PDO::PARAM_BOOL) {
                $icons .= glyphHelper::get(glyphHelper::ADJUST);
            } elseif ($type === \PDO::PARAM_INT) {
                $icons .= glyphHelper::get(glyphHelper::BARCODE);
            } else {
                $icons .= glyphHelper::get(glyphHelper::ALIGN_LEFT);
            }
            $labelIcon = $icons . $fieldName;
            $this->setLabel($fieldName, $labelIcon);
        }
    }

    /**
     * _setFieldsOptions
     *
     */
    private function _setFieldsOptions()
    {
        $booleanValues = [
            $this->booleanValues[0] => 'Oui',
            $this->booleanValues[1] => 'Non'
        ];
        foreach ($this->_fieldList as $fieldName) {
            $type = $this->_fieldTypesList[$fieldName];
            $options = ($type === \PDO::PARAM_INT) ? ['type' => 'number'] : [];
            $isBool = ($type === \PDO::PARAM_BOOL);
            if ($isBool) {
                $this->setType($fieldName, 'select');
                $this->setData($fieldName, $booleanValues);
                $isPosted = isset($this->postedData[$fieldName]);
                $isBoolTrue = ($isPosted && $isBool && $this->postedData[$fieldName] === $this->booleanValues[0]);
                $this->setValue($fieldName, ($isBoolTrue) ? $this->booleanValues[0] : $this->booleanValues[1]);
            }
            $this->setElementOptions($fieldName, $options);
        }
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $elementWrapper = 'form-element-wrapper';
        $cols6 = $elementWrapper . ' col-sm-6';
        $formControl = 'form form-control';
        foreach ($this->_fieldList as $field) {
            if ($field != 'id') {
                $this->setWrapperClass($field, $cols6);
                $this->setClass($field, $formControl);
            }
        }
    }

    /**
     * _setOperators
     *
     */
    private function _setOperators()
    {
        $fieldsOperator = array_filter(
            $this->_getFields(),
            array($this, '_needOperator')
        );
        foreach ($fieldsOperator as $fieldOperator) {
            $opValue = (isset($this->_operations[$fieldOperator])) ? $this->_operations[$fieldOperator] : '=';
            $this->setOperator($fieldOperator, $opValue);
            $this->setClass($fieldOperator, 'left');
        }
    }
}
