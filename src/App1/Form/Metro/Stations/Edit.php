<?php
/**
 * App1\Form\Metro\Stations\Edit
 *
 * @author pierrefromager
 */
namespace App1\Form\Metro\Stations;

use Pimvc\Form;
use Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Tools\Session as sessionTool;
use App1\Model\Metro\Stations as modelStations;

class Edit extends Form
{
    const METRO_STATIONS_EDIT_ACTION = 'metro/stations/edit';
    const METRO_STATIONS_EDIT_FORM_NAME = 'metro-stations-edit';
    const METRO_STATIONS_EDIT_FORM_METHOD = 'post';
    const METRO_STATIONS_EDIT_DECORATOR_BREAK = '<br style="clear:both"/>';
    const FORM_WRAP_CTRL = 'form form-control';
    const FORM_ELT_WRAP = 'form-element-wrapper';

    protected $isAdmin;
    protected $uid;
    protected $stationsModel;
    protected $postedData;
    protected $app;
    protected $baseUrl;
    protected $modelConfig;

    /**
     * __construct
     *
     * @param array $postedDatas
     * @param int $uid
     * @param string $mode
     * @return App1\Form\Metro\Lignes\Edit
     */
    public function __construct($postedDatas, $uid, $mode = '')
    {
        $this->app = \Pimvc\App::getInstance();
        $this->baseUrl = $this->app->getRequest()->getBaseUrl();
        $this->modelConfig = $this->app->getConfig()->getSettings('dbPool');
        $this->stationsModel = new modelStations($this->modelConfig);
        $this->isAdmin = sessionTool::isAdmin();
        $this->postedData = $postedDatas;
        $this->setMode($mode);
        parent::__construct(
            $this->_getFields(),
            self::METRO_STATIONS_EDIT_FORM_NAME,
            $this->baseUrl . DIRECTORY_SEPARATOR . self::METRO_STATIONS_EDIT_ACTION,
            self::METRO_STATIONS_EDIT_FORM_METHOD,
            $this->postedData
        );
        $this->_setWrappers();
        $this->setLabels($this->_getLabels());
        $this->setAlign('left');
        $this->Setsectionsize(20);
        $this->setMode($mode);
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Enregistrer');
        $this->render();
        unset($this->stationsModel);
        return $this;
    }

    /**
     * _setWrappers
     *
     */
    private function _setWrappers()
    {
        $cols3 = self::FORM_ELT_WRAP . ' col-sm-3';
        $this->setWrapperClass(modelStations::_H, $cols3);
        $this->setClass(modelStations::_H, self::FORM_WRAP_CTRL);
        $this->setWrapperClass(modelStations::_NAME, $cols3);
        $this->setClass(modelStations::_NAME, self::FORM_WRAP_CTRL);
        $this->setWrapperClass(modelStations::_LAT, $cols3);
        $this->setClass(modelStations::_LAT, self::FORM_WRAP_CTRL);
        $this->setWrapperClass(modelStations::_LON, $cols3);
        $this->setClass(modelStations::_LON, self::FORM_WRAP_CTRL);
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return $this->stationsModel->getColumns();
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
            modelStations::_NAME => ucfirst(modelStations::_NAME),
            modelStations::_LAT => ucfirst(modelStations::_LAT),
            modelStations::_LON => ucfirst(modelStations::_LON),
            modelStations::_H => 'HStation',
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
            'id' => glyphHelper::get(glyphHelper::TAG),
            modelStations::_NAME => glyphHelper::get(glyphHelper::TAG),
            modelStations::_H => glyphHelper::get(glyphHelper::TAGS),
            modelStations::_LAT => glyphHelper::get(glyphHelper::MAP_MARKER),
            modelStations::_LON => glyphHelper::get(glyphHelper::MAP_MARKER),
        );
        return isset($icons[$fieldName]) ? $icons[$fieldName] : '';
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
}
