<?php

/**
 * App1\Form\Crud\Select
 *
 * @author pierrefromager
 */

namespace App1\Form\Crud;

use Pimvc\Form;
use Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Tools\Session as sessionTool;

class Select extends Form
{

    const CRUD_SELECT_ACTION = 'crud/index';
    const CRUD_SELECT_FORM_NAME = 'crud-select-table';
    const CRUD_SELECT_FORM_METHOD = 'POST';
    const CRUD_SELECT_DECORATOR_BREAK = '<br style="clear:both"/>';
    const _DB_POOL = 'dbPool';
    const _SLOT = 'slot';
    const _TABLE = 'table';
    const _LABEL = 'label';

    protected $isAdmin;
    protected $postedData;
    protected $app;
    protected $baseUrl;
    private $_slotList;
    private $_selectedSlot;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @return App1\Form\Crud\Select
     */
    public function __construct($postedDatas)
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->isAdmin = sessionTool::isAdmin();
        $this->postedData = $postedDatas;
        
        parent::__construct(
            $this->_getFields(),
            self::CRUD_SELECT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::CRUD_SELECT_ACTION,
            self::CRUD_SELECT_FORM_METHOD,
            $this->postedData
        );

        $this->setSlotList();
        if (!isset($this->postedData[self::_SLOT])) {
            $this->postedData[self::_SLOT] = array_keys($this->_slotList)[0];
        }

        $this->setType(self::_SLOT, 'select');
        $this->setData(self::_SLOT, $this->_slotList);
        $this->setType(self::_TABLE, 'select');
        $this->setData(self::_TABLE, $this->getTableNames());

        $this->_setWrappers();
        $this->setLabels($this->_getLabels());

        $this->setValues($this->postedData);
        $this->setValidLabelButton('Accéder');
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
     * _getValidators
     *
     * @return type
     */
    private function _getValidators()
    {
        return [
            self::_SLOT => 'isrequired',
            self::_TABLE => 'isrequired',
        ];
    }

    /**
     * setSlotList
     */
    private function setSlotList()
    {
        $slots = $this->app->getConfig()->getSettings(self::_DB_POOL);
        $keys = array_keys($slots);
        $values = array_map(function ($v) {
            return $v[self::_LABEL];
        }, $slots);
        $this->_slotList = array_combine($keys, $values);
    }

    /**
     * getTableNames
     * @return array
     */
    private function getTableNames(): array
    {
        $forge = new \Pimvc\Db\Model\Forge($this->postedData[self::_SLOT]);
        $tablesName = $forge->showTables();
        sort($tablesName);
        unset($forge);
        return \Pimvc\Tools\Arrayproto::getTupple($tablesName);
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
        $this->setWrapperClass(self::_TABLE, $cols6);
        $this->setClass(self::_TABLE, $formControl);
        $this->setWrapperClass(self::_SLOT, $cols6);
        $this->setClass(self::_SLOT, $formControl);
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [self::_SLOT, self::_TABLE];
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
            self::_SLOT => 'Slot',
            self::_TABLE => 'Nom table',
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
            self::_SLOT => glyphHelper::get(glyphHelper::SHARE_ALT),
            self::_TABLE => glyphHelper::get(glyphHelper::TH_LIST),
        );
        return isset($icons[$fieldName]) ? $icons[$fieldName] : '';
    }
}
