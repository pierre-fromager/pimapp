<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Dropdown
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

use \Pimvc\Html\Element\Decorator as Deco;
use \Pimvc\Views\Helpers\Glyph as glyphHelper;

class Dropdown
{
    const PARAM_EMPTY = '';
    const PARAM_CLASS = 'class';
    const PARAM_ID = 'id';
    const PARAM_HREF = 'href';
    const PARAM_DATA_TOGGLE = 'data-toggle';
    const PARAM_ROLE = 'role';
    const PARAM_LABEL = 'label';
    const PARAM_LINK = 'link';
    const PARAM_TYPE = 'type';
    const PARAM_STYLE = 'style';
    const PARAM_HEADER = 'header';
    const TAG_A = 'a';
    const TAG_B = 'b';
    const TAG_UL = 'ul';
    const TAG_LI = 'li';
    const TAG_DIV = 'div';
    const DROPDOWN_ID = 'dropdown-id';
    const DROPDOWN_CLASS = 'dropdown';
    const DROPDOWN_MENU_CLASS = 'dropdown-menu';
    const DROPDOWN_MENU_LARGE_CLASS = 'dropdown-menu-large';
    const DROPDOWN_TOGGLE_CLASS = 'dropdown-toggle';
    const DROPDOWN_CARET_CLASS = 'caret';
    const DROPDOWN_DIVIDER_CLASS = 'divider';
    const DROPDOWN_HEADER_CLASS = 'dropdown-header';
    const DROPDOWN_HEADER_ROLE = 'presentation';
    
    private $id;
    private $linkId;
    private $icon;
    private $label;
    private $datas;
    private $content;
    private $itemLinkClass;
    private $itemClass;
    private $menuItemClass;
    private $multiColumnType;

    /**
     * __construct
     *
     * @param string $icon
     * @param string $label
     * @param string $datas
     */
    public function __construct($icon, $label, $datas)
    {
        $this->icon = $icon;
        $this->label = $label;
        $this->datas = $datas;
        $this->content = self::PARAM_EMPTY;
        $this->setId();
        $this->setLinkId();
        $this->setMenuItemClass();
        return $this;
    }
    
    /**
     * setId
     *
     * @param string $id
     */
    public function setId($id = '')
    {
        $this->id = empty($id) ? md5(serialize($this->datas)) : $id;
    }
    
    /**
     * setMultiColumnType
     *
     * @param string $multiColumnType
     */
    public function setMultiColumnType($multiColumnType = 'col-sm-3')
    {
        $this->multiColumnType = $multiColumnType;
        return $this;
    }

    /**
     * setLinkId
     *
     * @param string $id
     */
    public function setLinkId($id = '')
    {
        $this->linkId = empty($id) ? md5($this->label) : $id;
    }
    
    /**
     * setMenuItemClass
     *
     * @param sring $class
     */
    public function setMenuItemClass($class = self::DROPDOWN_MENU_CLASS)
    {
        $this->menuItemClass = $class;
        return $this;
    }
    
    /**
     * setItemClass
     *
     * @param string $class
     */
    public function setItemClass($class = '')
    {
        $this->itemClass = $class;
    }

    /**
     * render
     *
     */
    public function render()
    {
        $this->content = $this->getMain(
            $this->getMainLink() . $this->getMenu($this->getItems())
        );
    }
    
    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->content;
    }
    
    /**
     * setItemLinkClass
     *
     * @param string $class
     */
    public function setItemLinkClass($class)
    {
        $this->itemLinkClass = $class;
    }
    
    /**
     * @see __destruct
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }


    /**
     * getMainLink
     *
     * @return string
     */
    private function getMainLink()
    {
        $caret = (string) new Deco(
            self::TAG_B,
            self::PARAM_EMPTY,
            array(self::PARAM_CLASS => self::DROPDOWN_CARET_CLASS)
        );
        return PHP_EOL . (string) new Deco(
            self::TAG_A,
            PHP_EOL . glyphHelper::get($this->icon) . $this->label . $caret,
            array(
                self::PARAM_HREF => '#'
                , self::PARAM_CLASS => self::DROPDOWN_TOGGLE_CLASS
                , self::PARAM_DATA_TOGGLE => self::DROPDOWN_CLASS
                , self::PARAM_ID => $this->linkId
            )
        );
    }
    
    /**
     * getMenu
     *
     * @param string $items
     * @return string
     */
    private function getMenu($items)
    {
        return (string) new Deco(
            self::TAG_UL,
            $items,
            array(
                self::PARAM_CLASS => $this->menuItemClass
            )
        );
    }

    /**
     * getItems
     *
     * @return string
     */
    private function getItems()
    {
        $items = self::PARAM_EMPTY;
        $wrappedItems = self::PARAM_EMPTY;
        $stack = array();
        $cpt = 0;
        foreach ($this->datas as $k => $data) {
            $hasType = (isset($data[self::PARAM_TYPE]));
            $type = ($hasType)
                ? $data[self::PARAM_TYPE]
                : self::PARAM_EMPTY;
            switch ($type) {
                case self::DROPDOWN_DIVIDER_CLASS:
                    $items .= $this->getDivider();
                    break;
                case self::PARAM_HEADER:
                    $items .= $this->getHeader($data[self::PARAM_LABEL]);
                    ++$cpt;
                    break;
                default:
                    $items .= $this->getItem(
                        $data[self::PARAM_LABEL],
                        $data[self::PARAM_LINK]
                    );
                    break;
            }
            
            if (isset($stack[$cpt])) {
                $stack[$cpt] .= $items;
            } else {
                $stack[$cpt] = $items;
            }
            $items = '';
        }
        foreach ($stack as $itemGroup) {
            $wrappedItems .= $this->_getWrapper(
                $this->_getGroupedItem($itemGroup)
            ) ;
        }
        return $wrappedItems;
    }
    
    /**
     * _getWrapper
     *
     * @param string $content
     * @return string
     */
    private function _getWrapper($content)
    {
        $options = array(
            self::PARAM_CLASS => 'bs-multicolumn-wrapper ' . $this->multiColumnType
        );
        return (string) new Deco(
            self::TAG_DIV,
            $content,
            $options
        );
    }
    
    /**
     * _getGroupedItem
     *
     * @param string $content
     * @return string
     */
    private function _getGroupedItem($content)
    {
        $options = array(
            self::PARAM_CLASS => 'bs-multicolumn-group-item'
        );
        return (string) new Deco(
            self::TAG_UL,
            $content,
            $options
        );
    }

    /**
     * getItem
     *
     * @param string $label
     * @param string $link
     * @return string
     */
    private function getItem($label, $link)
    {
        return (string) new Deco(
            self::TAG_LI,
            (string) new Deco(
                self::TAG_A,
                $label,
                array(
                    self::PARAM_HREF => $link
                    , self::PARAM_CLASS => $this->itemLinkClass
                    , self::PARAM_STYLE => 'color:black'
                )
            ) . PHP_EOL,
            array(self::PARAM_CLASS => $this->itemClass)
        );
    }
    
    /**
     * getDivider
     *
     * @return string
     */
    private function getDivider()
    {
        return (string) new Deco(
            self::TAG_LI,
            self::PARAM_EMPTY,
            array(
                self::PARAM_CLASS => self::DROPDOWN_DIVIDER_CLASS
                    . ' ' . $this->itemClass
            )
        );
    }
    
    /**
     * getHeader
     *
     * @param string $label
     * @return string
     */
    private function getHeader($label)
    {
        return (string) new Deco(
            self::TAG_LI,
            $label,
            array(
                self::PARAM_CLASS => self::DROPDOWN_HEADER_CLASS
                    . ' ' . $this->itemClass
                , self::PARAM_ROLE => self::DROPDOWN_HEADER_ROLE
            )
        );
    }
  
    /**
     * getMain
     *
     * @param string $mainContent
     * @return string
     */
    private function getMain($mainContent)
    {
        return (string) new Deco(
            self::TAG_LI,
            $mainContent,
            array(
                self::PARAM_ID => $this->id
                , self::PARAM_CLASS => self::DROPDOWN_CLASS
            )
        );
    }
}
