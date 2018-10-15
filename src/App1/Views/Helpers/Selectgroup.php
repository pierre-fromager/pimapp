<?php

/**
 * App1\Views\Helpers\Selectgroup
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 */
namespace App1\Views\Helpers;

use Pimvc\Tools\Arrayproto;

class Selectgroup
{

    const OPTION_OPEN_VALUE = '<option value="';
    const OPTION_END = '">';
    const OPTION_CLOSE_VALUE = '</option>';
    const SELECT_OPEN_VALUE = '<select id="';
    const SELECT_CLOSE_VALUE = '</select>';
    const OPTION_SELECTED = '" selected >';
    const OPTION_DEFAULT_MESSAGE = '- Selectionner -';
    const OPTION_GROUP_OPEN = '<optgroup label="';
    const OPTION_GROUP_CLOSE = '</optgroup>';
    const CR = "\n";
    
    protected $_id = '';
    protected $_name = '';
    protected $_selected = '';
    protected $_params = array();
    protected $_content = '';

    /**
     * @see __construct
     *
     * @param string $name
     * @param string $id
     * @param string $selected
     * @param array $params
     */
    public function __construct($name, $id, $selected, $params)
    {
        $this->_name = $name;
        $this->_id = $id;
        $this->_selected = $selected;
        $this->_params = $params;
        if (!empty($params)) {
            $this->process();
        }
    }
    
    /**
     * process set _content
     */
    protected function process()
    {
        $select = self::SELECT_OPEN_VALUE . $this->_id
            . '" name = "' . $this->_name . '">';
        $params = $this->_params;
        $options = self::OPTION_OPEN_VALUE . self::OPTION_END
            . self::OPTION_DEFAULT_MESSAGE . self::OPTION_CLOSE_VALUE
            . self::CR;
        foreach ($params as $group => $vgroup) {
            $options .= self::OPTION_GROUP_OPEN . $group . '">';
            $options .= self::getOptions($vgroup);
            $options .= self::OPTION_GROUP_CLOSE;
        }
        $select .= $options . self::SELECT_CLOSE_VALUE;
        $this->_content = $select;
    }
    
    /**
     * returns _content string
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->_content;
    }

    /**
     * getOptions returns html options string
     *
     * @param array $options
     * @return string
     */
    private function getOptions($options)
    {
        $tupple = Arrayproto::getTupple($options);
        $optionsContent = '';
        foreach ($tupple as $key => $value) {
            $selected = ($value == $this->_selected)
                ? self::OPTION_SELECTED
                : self::OPTION_END;
            $optionsContent .= self::getOption($key, $value, $selected);
        }
        return $optionsContent;
    }
    
    /**
     * getOption returns single option string
     *
     * @param string $key
     * @param string $value
     * @param string $selected
     * @return string
     */
    private static function getOption($key, $value, $selected)
    {
        return self::OPTION_OPEN_VALUE
            . $value . $selected . $key
            . self::OPTION_CLOSE_VALUE;
    }
}
