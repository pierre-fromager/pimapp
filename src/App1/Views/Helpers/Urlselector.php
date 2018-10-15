<?php

/**
 * Description of App1\Views\Helpers\Urlselector
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 */
namespace App1\Views\Helpers;

use Pimvc\Tools\Arrayproto;

class Urlselector
{
    const OPTION_OPEN_VALUE = '<option value="';
    const OPTION_END = '">';
    const OPTION_CLOSE_VALUE = '</option>';
    const SELECT_OPEN_VALUE = '<select class="col-lg-6 form-control" name="';
    const SELECT_CLOSE_VALUE = '</select>';
    const OPTION_SELECTED = '" selected >';
    const OPTION_DEFAULT_MESSAGE = '- Selectionner -';
    const CR = "\n";
    
    private static $selected = null;

    /**
     * get
     *
     * @param string $name
     * @param string $url
     * @param array $options
     * @param string $default
     * @param string $defaultMessage
     * @return string
     */
    public static function get($name, $url, $options, $default, $defaultMessage = '')
    {
        self::$selected = $default;
        $selector = self::SELECT_OPEN_VALUE . $name . '" '
            . 'onchange="location.href='
            . "'" . $url . "' +"
            . 'this.options[this.selectedIndex].value;">'
            . self::CR;
        $selector .= self::OPTION_OPEN_VALUE . $defaultMessage .self::OPTION_END
                . self::OPTION_DEFAULT_MESSAGE . self::OPTION_CLOSE_VALUE
                . self::CR;
        $selector .= self::getOptions($options, $default);
        $selector .= self::SELECT_CLOSE_VALUE . self::CR;
        return $selector;
    }
    
    /**
     * getOptions
     *
     * @param array $options
     * @param string $default
     * @return string
     */
    private static function getOptions($options, $default)
    {
        $optionsContent = '';
        $options = Arrayproto::getTupple($options);
        foreach ($options as $key => $value) {
            $selected = ($value == self::$selected)
                ? self::OPTION_SELECTED
                : self::OPTION_END;
            $optionsContent .= self::getOption($key, $value, $selected);
        }
        return $optionsContent;
    }
    
    /**
     * getOption
     *
     * @param string $key
     * @param string $value
     * @param string $selected
     * @return string
     */
    private static function getOption($key, $value, $selected)
    {
        return self::OPTION_OPEN_VALUE
            . $value
            . $selected
            . $key
            . self::OPTION_CLOSE_VALUE;
    }
}
