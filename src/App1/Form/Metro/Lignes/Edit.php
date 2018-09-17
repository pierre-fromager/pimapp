<?php
/**
 * Form_Users_Edit
 *
 * @author pierrefromager
 */
namespace App1\Form\Metro\Lignes;

use Pimvc\Form;
use Pimvc\Views\Helpers\Glyph as glyphHelper;
use Pimvc\Tools\Session as sessionTool;
use App1\Model\Metro\Lignes as modelLignes;

class Edit extends Form
{
    const LINE_EDIT_ACTION = 'metro/lignes/edit';
    const LINE_EDIT_FORM_NAME = 'metro-lignes-edit';
    const LINE_EDIT_FORM_METHOD = 'post';
    const LINE_EDIT_DECORATOR_BREAK = '<br style="clear:both"/>';

    protected $isAdmin;
    protected $uid;
    protected $lignesModel;
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
        $this->lignesModel = new modelLignes($this->modelConfig);
        $this->isAdmin = sessionTool::isAdmin();
        $this->postedData = $postedDatas;
        $this->setMode($mode);
        parent::__construct(
            $this->_getFields(),
            self::LINE_EDIT_FORM_NAME, $this->baseUrl . DIRECTORY_SEPARATOR . self::LINE_EDIT_ACTION, self::LINE_EDIT_FORM_METHOD, $this->postedData
        );
        $this->_setWrappers();
        $this->setLabels($this->_getLabels());
        $this->setAlign('left');
        $this->Setsectionsize(20);
        $this->setMode($mode);
        $this->setValues($this->postedData);
        $this->setValidLabelButton('Enregistrer');
        $this->render();
        unset($this->lignesModel);
        return $this;
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
        $this->setWrapperClass(modelLignes::_LIGNE, $cols6);
        $this->setClass(modelLignes::_LIGNE, $formControl);
        $this->setWrapperClass(modelLignes::_DIST, $cols6);
        $this->setClass(modelLignes::_DIST, $formControl);
        $this->setWrapperClass(modelLignes::_SRC, $cols6);
        $this->setClass(modelLignes::_SRC, $formControl);
        $this->setWrapperClass(modelLignes::_HSRC, $cols6);
        $this->setClass(modelLignes::_HSRC, $formControl);
        $this->setWrapperClass(modelLignes::_DST, $cols6);
        $this->setClass(modelLignes::_DST, $formControl);
        $this->setWrapperClass(modelLignes::_HDST, $cols6);
        $this->setClass(modelLignes::_HDST, $formControl);
    }

    /**
     * _getFields
     *
     * @return array
     */
    private function _getFields()
    {
        return [
            modelLignes::_LIGNE,
            modelLignes::_DIST,
            modelLignes::_SRC,
            modelLignes::_HSRC,
            modelLignes::_DST,
            modelLignes::_HDST
        ];
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
            modelLignes::_LIGNE => 'Line',
            modelLignes::_DIST => 'Distance',
            modelLignes::_HSRC => 'HDeparture',
            modelLignes::_HDST => 'HArrival',
            modelLignes::_SRC => 'Departure',
            modelLignes::_DST => 'Arrival',
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
            modelLignes::_LIGNE => glyphHelper::get(glyphHelper::LINK),
            modelLignes::_SRC => glyphHelper::get(glyphHelper::MAP_MARKER),
            modelLignes::_DST => glyphHelper::get(glyphHelper::MAP_MARKER),
            modelLignes::_HSRC => glyphHelper::get(glyphHelper::TAG),
            modelLignes::_HDST => glyphHelper::get(glyphHelper::TAG),
            modelLignes::_DIST => glyphHelper::get(glyphHelper::FORWARD),
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
