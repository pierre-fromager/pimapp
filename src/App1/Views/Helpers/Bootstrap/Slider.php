<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Slider
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

class Slider
{
    const JS_PATH = 'public/js/bootstrap/';
    const CSS_PATH = 'public/css/bootstrap/';
    const ID_PREFIX = 'field_';
    const TAB = "\t";
    const JQ_SLIDER_NAMESPACE = 'bootstrapSlider';
    const CSS_SLIDER_PLUGIN = 'bootstrap-slider.css';
    const JS_SLIDER_PLUGIN = 'bootstrap-slider.js';
    const JQ_OBSERVER_NAMESPACE = 'ObserverSliders';
    const JS_OBSERVER_SLIDER_PLUGIN = 'public/js/observer/sliders.js';
    const OBSERVER_CLASS = '.slider';
    const OBSERVER_OPTIONS = 'change';

    protected $fields;
    protected $observableClass;
    protected $observableOptions;
    protected $observableCallback;
    protected static $hasRessource;

    /**
     * __construct
     *
     * @param string $fields
     */
    public function __construct($fields = array())
    {
        $this->setFields($fields);
    }
    
    /**
     * setFields
     *
     * @param string $fields
     */
    public function setFields($fields)
    {
        $this->fields = $fields;
    }
    
    /**
     * setObservableClass
     *
     * @param string $observableClass
     */
    public function setObservableClass($observableClass = '')
    {
        $this->observableClass = (empty($observableClass))
            ? self::OBSERVER_CLASS
            : $observableClass;
    }
    
    /**
     * setObservableOptions
     *
     * @param string $observableClass
     */
    public function setObservableOptions($observableOptions = '')
    {
        $this->observableOptions = (empty($observableOptions))
            ? self::OBSERVER_OPTIONS
            : $observableOptions;
    }
    
    /**
     * setObservableCallback
     *
     * @param string $observableCallback
     */
    public function setObservableCallback($observableCallback = '')
    {
        $this->observableCallback = (empty($observableCallback))
            ? 'function(){}'
            : $observableCallback;
    }

    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        $this->loadRessources();
        $this->content = '<script>'. PHP_EOL;
        $this->content .= '$j(document).ready(function() {' . PHP_EOL;
        foreach ($this->fields as $field) {
            $this->content .= self::TAB . $this->getSlider($field) . PHP_EOL;
        }
        $this->content .= $this->getObservablePlugin();
        $this->content .= '});' . PHP_EOL;
        $this->content .= '</script>'. PHP_EOL;
    }

    /**
     * __toString.
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->content;
    }
    
    /**
     * getObservablePlugin
     *
     * @return string
     */
    private function getObservablePlugin()
    {
        return ($this->observableClass && $this->observableOptions)
            ? '$j("' . $this->observableClass . '").'
                . self::JQ_OBSERVER_NAMESPACE
                . '(' . $this->observableOptions . ','
                . $this->observableCallback
                .');' . PHP_EOL
            : '';
    }

    /**
     * getSlider
     *
     * @param string $field
     * @return string
     */
    private function getSlider($field)
    {
        return '$j(' . "'#" . self::ID_PREFIX . $field . "'" . ').'
            . self::JQ_SLIDER_NAMESPACE . '({' . PHP_EOL
            . self::TAB . 'formatter: function(value) {' . PHP_EOL
            . self::TAB . self::TAB . "return 'Current value: ' + value;" . PHP_EOL
            . self::TAB . '}' . PHP_EOL
            . '});' . PHP_EOL;
    }

    /**
     * loadRessources
     *
     */
    private function loadRessources()
    {
        if (self::$hasRessource == null) {
            $jsPath = self::JS_PATH;
            $jsCollection = array(
                $jsPath . self::JS_SLIDER_PLUGIN
            );
            if ($this->observableClass) {
                $jsCollection[] = self::JS_OBSERVER_SLIDER_PLUGIN;
            }
            foreach ($jsCollection as $jsRes) {
                Helper_Collection_Js::add($jsRes);
            }
            Helper_Collection_Js::save();
            $cssPath = self::CSS_PATH;
            $cssCollection = array(
                $cssPath . self::CSS_SLIDER_PLUGIN
            );
            foreach ($cssCollection as $cssRes) {
                Helper_Collection_Css::add($cssRes);
            }
            Helper_Collection_Css::save();
            self::$hasRessource == true;
        }
    }
}
