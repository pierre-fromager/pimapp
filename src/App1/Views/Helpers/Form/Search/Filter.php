<?php

/**
 * Description of App1\Views\Helpers\Form\Search\Filter
 *
 * @author pierrefromager
 */
namespace App1\Views\Helpers\Form\Search;

class Filter
{
    const DECORATOR_ICON = 'i';
    const DECORATOR_WRAPPER_TITLE = 'div';
    const DECORATOR_TITLE = 'span';
    const DECORATOR_TARGET = self::DECORATOR_TITLE;
    const PARAM_TITLE = 'title';
    const PARAM_ID = 'id';
    const PARAM_CLASS = 'class';
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
    public static function get($content, $options = array())
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
        self::$options[self::PARAM_TITLE] = (isset($options[self::PARAM_TITLE]))
            ? $options[self::PARAM_TITLE]
            : self::DEFAULT_TITLE;
        self::$options[self::PARAM_ID] = (isset($options[self::PARAM_ID]))
            ? $options[self::PARAM_ID]
            : self::DEFAULT_ID;
        self::$options[self::PARAM_CLASS] = (isset($options[self::PARAM_CLASS]))
            ? $options[self::PARAM_CLASS]
            : self::DEFAULT_CLASS;
        self::$title = self::$options[self::PARAM_TITLE];
        self::$id = self::$options[self::PARAM_ID];
        self::$class = self::$options[self::PARAM_CLASS];
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
            . PHP_EOL . '</div>'. PHP_EOL . '</div>' . PHP_EOL;
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
        $iconClass= self::PARAM_GLYPHICON . self::DEFAULT_SPACE . $class;
        $options = array(self::PARAM_CLASS => $iconClass);
        $span = new \Pimvc\Html\Element\Decorator(
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
        $options = array(
            self::PARAM_ID => self::ID_LESS
            , self::PARAM_CLASS => self::DEFAULT_CLASS_BUTTON
        );
        $link = new \Pimvc\Html\Element\Decorator(
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
        $spanTitle = new \Pimvc\Html\Element\Decorator(
            self::DECORATOR_TITLE,
            self::$title
        );
        $wrapperOption = array(
            self::PARAM_ID => self::$id
            , self::PARAM_CLASS => 'row-fluid'
        );
        $wrapperTitle = new \Pimvc\Html\Element\Decorator(
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
        $options = array(self::PARAM_ID => self::DEFAULT_TARGET_ID);
        $span = new \Pimvc\Html\Element\Decorator(
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
