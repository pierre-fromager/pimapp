<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Tab
 *
 * Convert associative array considering
 * key as tab headers and value as tab content for bootstrap 3
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

use Pimvc\Html\Element\Decorator;

class Tab
{
    const PARAM_DIV = 'div';
    const PARAM_ID = 'id';
    const PARAM_A = 'a';
    const PARAM_HREF = 'href';
    const PARAM_P = 'p';
    const PARAM_UL = 'ul';
    const PARAM_LI = 'li';
    const PARAM_CLASS = 'class';
    const EMPTY_VAL = '';
    const TAB_ID_PREFIX = 'tab-';
    const TAB_CLASS = 'nav nav-tabs nav-justified';
    const TAB_ITEM_CLASS = 'tab-pane fade';
    const GEN_MIN = 2783783;
    const GEN_MAX = 3783783;

    protected $headers = array();
    protected $content = array();
    
    private $data = null;
    protected $class;
    protected $paneClass;
    protected $id = '';
    protected $html = '';
    protected $selected = '';
    protected $tabColor;

    /**
     * __construct
     *
     * @param array $datas
     */
    public function __construct($datas, $selected = '')
    {
        $this->data = $datas;
        $this->headers = array_keys($datas);
        $this->content = $datas;
        $this->setSelected($selected);
        $this->setId(self::TAB_ID_PREFIX . rand(self::GEN_MIN, self::GEN_MAX));
        $this->setClass();
    }
    
    /**
     * setSelected
     *
     * @param string $selected
     */
    public function setSelected($selected)
    {
        $this->selected = $selected;
        return $this;
    }

    /**
     * setId
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    
    /**
     * setClass
     *
     * @param string $class
     */
    public function setClass($class = self::TAB_CLASS)
    {
        $this->class = $class;
    }
    
    /**
     * setPaneClass
     *
     * @param string $class
     */
    public function setPaneClass($class = self::TAB_ITEM_CLASS)
    {
        $this->paneClass = $class;
        return $this;
    }
    
    /**
     * setTabColor
     *
     * @param string $color
     */
    public function setTabColor($color = '')
    {
        $this->tabColor = empty($color) ? '' : '';
        return $this;
    }

    /**
     * render
     *
     */
    public function render()
    {
        $this->html = $this->getList($this->getTitle($this->headers))
            . $this->getBlock('', 'tab-content', $this->getBody($this->headers)) . PHP_EOL
            . $this->getScript();
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->html;
    }

    /**
     * @see __destruct
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    /**
     * getTitle
     *
     * @param array $headers
     * @return string
     */
    private function getTitle($headers)
    {
        $titles = '';
        foreach ($headers as $header) {
            $titles .= $this->getItem($header);
        }
        return $titles;
    }

    /**
     * getBody
     *
     * @param array $headers
     * @return string
     */
    private function getBody($headers)
    {
        $body = '';
        foreach ($headers as $header) {
            $body .= $this->getBlock(
                md5($header),
                $this->paneClass,
                $this->getPara($this->data[$header])
            ) . PHP_EOL;
        }
        return $body;
    }

    /**
     * getBlock
     *
     * @param string $id
     * @param string $class
     * @param string $content
     * @return string
     */
    private function getBlock($id, $class, $content)
    {
        return $this->getTag(self::PARAM_DIV, $content, [
            self::PARAM_ID => $id
            , self::PARAM_CLASS => $class
        ]);
    }

    /**
     * getList
     *
     * @param string $content
     * @return string
     */
    private function getList($content)
    {
        return $this->getTag(self::PARAM_UL, $content, [
            self::PARAM_CLASS => $this->class
            , self::PARAM_ID => $this->id
        ]);
    }

    /**
     * getItem
     *
     * @param string $item
     * @return string
     */
    private function getItem($item)
    {
        return $this->getTag(self::PARAM_LI, $this->getLink($item));
    }

    /**
     * getLink
     *
     * @param string $item
     * @return string
     */
    private function getLink($item)
    {
        return $this->getTag(self::PARAM_A, $item, [
            self::PARAM_HREF => '#' . md5($item),
            'data-toggle' => 'tab'
        ]);
    }

    /**
     * getPara
     *
     * @param string $content
     * @return string
     */
    private function getPara($content)
    {
        return $this->getTag(self::PARAM_P, $content);
    }

    /**
     * getTag
     *
     * @param string $tag
     * @param string $content
     * @return string
     */
    private function getTag($tag, $content, $attribs = [])
    {
        return new Decorator($tag, $content, $attribs);
    }

    /**
     * getScript
     *
     * @return string
     */
    private function getScript()
    {
        $hashSelected = md5($this->selected);
        $selected = ((empty($this->selected)))
            ? ''
            : 'var indexTab = $j(\'#' . $this->id
                . ' a[href="#' . $hashSelected . '"]\').parent().index();'
                . '$j("#' . $this->id . '").tabs("select", indexTab);';
        $selector = ((empty($this->selected)))
            ? ''
            //: '$j(\'#' . $hashSelected . '\').tab(\'show\');';
            : '$j(\'.nav-tabs a[href="#' . $hashSelected . '"]\').tab(\'show\');';
        $script = '<script type="text/javascript">'
            . ' $j(function () {'
            . $selector
            . '})'
            . '</script>';
        return $script;
    }
}
