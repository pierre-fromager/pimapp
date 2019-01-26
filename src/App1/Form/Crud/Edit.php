<?php

/**
 * App1\Form\Crud\Edit
 *
 * @author pierrefromager
 */

namespace App1\Form\Crud;

use Pimvc\Form;
use \Pimvc\Db\Model\Field as modelField;
use \Pimvc\Db\Model\Fields as modelFields;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;

class Edit extends Form
{

    const CRUD_EDIT_ACTION = 'crud/edit';
    const CRUD_EDIT_FORM_NAME = 'crud-edit';
    const CRUD_EDIT_FORM_METHOD = 'POST';
    const CRUD_EDIT_DECORATOR_BREAK = '<br style="clear:both"/>';

    protected $isAdmin;
    protected $postedData;
    protected $app;
    protected $baseUrl;
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
     * @param modelFields $modelFields
     * @param string $adapter
     * @return App1\Form\Crud\Edit
     */
    public function __construct(array $postedDatas, modelFields $modelFields, string $adapter)
    {
        $this->modelFields = $modelFields;
        $this->adapter = $adapter;
        if ($this->is4d()) {
            $this->booleanValues = ['TRUE', 'FALSE'];
        }
        $this->_fieldList = $this->getFieldList();
        $this->_fieldTypesList = $this->modelFields->getPdos();
        $this->_fieldIndexes = $this->modelFields->getIndexes(true);
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->postedData = $postedDatas;
        parent::__construct(
            $this->_fieldList,
            self::CRUD_EDIT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::CRUD_EDIT_ACTION,
            self::CRUD_EDIT_FORM_METHOD,
            $this->postedData
        );
        $this->_setFieldsOptions();
        $this->_setLabels();
        $this->_setWrappers();
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Valider');
        $this->render();
        return $this;
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
                $isBoolTrue = ($isBool && $this->postedData[$fieldName] === $this->booleanValues[0]);
                $this->setValue($fieldName, ($isBoolTrue) ? $this->booleanValues[0] : $this->booleanValues[1]);
            }
            $this->setElementOptions($fieldName, $options);
        }
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
     * getFieldList
     *
     * @return array
     */
    private function getFieldList()
    {
        return array_map(function (modelField $v) {
            return $v->getName();
        }, iterator_to_array($this->modelFields));
    }

    /**
     * is4d
     *
     * @return bool
     */
    private function is4d(): bool
    {
        return ($this->adapter === \Pimvc\Db\Model\Core::MODEL_ADAPTER_4D);
    }
}
