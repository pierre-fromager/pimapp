<?php

/**
 * Description of App1\Views\Helpers\Form\Search\Filter
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Form\Search;

use \Pimvc\Html\Element\Decorator as htmlElement;

class Filter
{

    const DECORATOR_ICON = 'i';
    const DECORATOR_WRAPPER_TITLE = 'div';
    const DECORATOR_TITLE = 'span';
    const DECORATOR_TARGET = self::DECORATOR_TITLE;
    const _TITLE = 'title';
    const _ID = 'id';
    const _CLASS = 'class';
    const PARAM_GLYPHICON = 'glyphicon';
    const DEFAULT_ID = 'toogle-filtrer';
    const ID_MORE = 'moreAction';
    const ID_LESS = 'lessAction';
    const DEFAULT_TARGET_ID = 'targetall';
    const DEFAULT_CLASS = 'toogle-filtrer';
    const DEFAULT_TITLE = 'Colonnes';
    const DEFAULT_CLASS_BUTTON = 'btn btn-default';
    const DEFAULT_CLASS_LEFT = 'glyphicon-chevron-left';
    const DEFAULT_CLASS_RIGHT = 'glyphicon-chevron-right';
    const DEFAULT_CLASS_UP = '';
    const DEFAULT_CLASS_DOWN = 'glyphicon-chevron-down';
    const DEFAULT_SPACE = ' ';

    private static $options;
    private static $title;
    private static $id;
    private static $class;
    private static $content;

    /**
     * get
     *
     * @param string $content
     * @return string
     */
    public static function get($content, $options = [])
    {
        self::$content = $content;
        self::setOptions($options);
        return self::$content . self::getHeader();
    }

    /**
     * setOptions
     *
     */
    private static function setOptions($options)
    {
        self::$options[self::_TITLE] = (isset($options[self::_TITLE])) ? $options[self::_TITLE] : self::DEFAULT_TITLE;
        self::$options[self::_ID] = (isset($options[self::_ID])) ? $options[self::_ID] : self::DEFAULT_ID;
        self::$options[self::_CLASS] = (isset($options[self::_CLASS])) ? $options[self::_CLASS] : self::DEFAULT_CLASS;
        self::$title = self::$options[self::_TITLE];
        self::$id = self::$options[self::_ID];
        self::$class = self::$options[self::_CLASS];
    }

    /**
     * getHeader
     *
     * @return string
     */
    private static function getHeader()
    {
        return self::getTitle()
                . '<div id="filtrer" class="row-fluid">'
                . PHP_EOL
                . '<div class="colonnes">' . PHP_EOL
                . self::getLinkLess(self::getLeft())
                . self::getTarget()
                . '<a id="' . self::ID_MORE . '" class="' . self::DEFAULT_CLASS_BUTTON . '">'
                . PHP_EOL . self::getRight() . PHP_EOL
                . '</a>'
                . PHP_EOL
                . '</div>'
                . PHP_EOL
                . '<div class="donnees">'
                . PHP_EOL . '</div>' . PHP_EOL . '</div>' . PHP_EOL;
        ;
    }

    /**
     * getIcon
     *
     * @param string $class
     * @return string
     */
    private static function getIcon($class)
    {
        $iconClass = self::PARAM_GLYPHICON . self::DEFAULT_SPACE . $class;
        $options = array(self::_CLASS => $iconClass);
        $span = new htmlElement(
            self::DECORATOR_ICON,
            self::DEFAULT_SPACE,
            $options
        );
        return (string) $span;
    }

    /**
     * getLinkLess
     *
     * @param type $content
     * @return type
     */
    private static function getLinkLess($content)
    {
        $options = [
            self::_ID => self::ID_LESS
            , self::_CLASS => self::DEFAULT_CLASS_BUTTON
        ];
        $link = new htmlElement(
            'a',
            $content,
            $options
        );
        return (string) $link;
    }

    /**
     * getTitle
     *
     * @return string
     */
    private static function getTitle()
    {
        $spanTitle = new htmlElement(
            self::DECORATOR_TITLE,
            ucfirst(self::$title)
        );
        $wrapperOption = array(
            self::_ID => self::$id
            , self::_CLASS => 'row-fluid'
        );
        $wrapperTitle = new htmlElement(
            self::DECORATOR_WRAPPER_TITLE,
            $spanTitle . PHP_EOL . self::getOpen() . PHP_EOL,
            $wrapperOption
        );
        return (string) $wrapperTitle;
    }

    /**
     * getTarget
     *
     * @return string
     */
    private static function getTarget()
    {
        $options = [self::_ID => self::DEFAULT_TARGET_ID];
        $span = new htmlElement(
            self::DECORATOR_ICON,
            self::DEFAULT_SPACE,
            $options
        );
        return (string) $span;
    }

    /**
     * getOpen
     *
     * @return string
     */
    private static function getOpen()
    {
        return self::getIcon(self::DEFAULT_CLASS_DOWN);
    }

    /**
     * getLeft
     *
     * @return string
     */
    private static function getLeft()
    {
        return self::getIcon(self::DEFAULT_CLASS_LEFT);
    }

    /**
     * getRight
     *
     * @return string
     */
    private static function getRight()
    {
        return self::getIcon(self::DEFAULT_CLASS_RIGHT);
    }
}
