<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Button
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

class Button
{
    const PARAM_CLASS = 'class';
    const PARAM_ID = 'id';
    const PARAM_STYLE = 'style';
    const PARAM_TYPE = 'type';
    const PARAM_BUTTON = 'button';
    const PARAM_VALUE = 'value';
    const PARAM_ROLE = 'role';
    const PARAM_HREF = 'href';
    const PARAM_DATALINK = 'data-link';
    const TAG_BUTTON = 'button';
    const TAG_A = 'a';
    const TAG_INPUT = 'input';
    const CLASS_BUTTON = 'btn';
    const TYPE_DEFAULT = 'btn-default';
    const TYPE_INFO = 'btn-info';
    const TYPE_PRIMARY = 'btn-primary';
    const TYPE_SUCCESS = 'btn-success';
    const TYPE_WARNING = 'btn-warning';
    const TYPE_DANGER = 'btn-danger';
    const TYPE_LINK = 'btn-link';
    const TYPE_BLOCK = 'btn-block';
    const SIZE_LARGE = 'btn-lg';
    const SIZE_MEDIUM = 'btn-md';
    const SIZE_SMALL = 'btn-sm';
    const SIZE_XSMALL = 'btn-xs';
    const ACTIVE = 'active';
    const DISABLED = 'disabled';

    protected $content;
    protected $tag;
    protected $title;
    protected $id;
    protected $class;
    protected $extraClass;
    protected $block;
    protected $active;
    protected $disabled;
    protected $size;
    protected $type;
    protected $datalink;
    protected $style;

    /**
     * __construct
     *
     * @param string $id
     * @param string $class
     * @param string $datalink
     */
    public function __construct($title)
    {
        $this->setTag('');
        $this->setTitle($title);
        $this->setId('');
        $this->setExtraClass('');
        $this->setType('');
        $this->setAsBlock(false);
        $this->setSize('');
        $this->setActive(false);
        $this->setDisabled(false);
        $this->style = '';
        $this->content = '';
        return $this;
    }

    /**
     * setTag
     *
     * @param string $title
     */
    public function setTag($tag)
    {
        $this->tag = ($tag) ? $tag : self::TAG_BUTTON;
        return $this;
    }

    /**
     * setTitle
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * setType
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = ($type) ? $type : self::TYPE_DEFAULT;
        return $this;
    }

    /**
     * setSize
     *
     * @param string $size
     */
    public function setSize($size)
    {
        $this->size = ($size) ? $size : self::SIZE_LARGE;
        return $this;
    }

    /**
     * setAsBlock
     *
     * @param boolean $size
     */
    public function setAsBlock($asBlock)
    {
        $this->block = ($asBlock) ? self::TYPE_BLOCK : '';
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
     * setExtraClass
     *
     * @param string $class
     */
    public function setExtraClass($extraClass)
    {
        $this->extraClass = $extraClass;
        return $this;
    }

    /**
     * setStyle
     *
     * @param string $style
     */
    public function setStyle($style)
    {
        $this->style = $style;
        return $this;
    }

    /**
     * setActive
     *
     * @param boolean $disabled
     */
    public function setActive($active)
    {
        $this->active = ($active === true) ? self::ACTIVE : '';
        return $this;
    }

    /**
     * setDisabled
     *
     * @param boolean $disabled
     */
    public function setDisabled($disabled)
    {
        $this->disabled = ($disabled === true) ? self::DISABLED : '';
        return $this;
    }

    /**
     * setDatalink
     *
     * @param string $datalink
     */
    public function setDatalink($datalink)
    {
        $this->datalink = $datalink;
        return $this;
    }

    /**
     * render
     *
     */
    public function render()
    {
        $this->setClass();
        switch ($this->tag) {
            case self::TAG_BUTTON:
                $options = array(
                    self::PARAM_TYPE => self::PARAM_BUTTON
                    , self::PARAM_DATALINK => $this->datalink
                );
                break;
            case self::TAG_A:
                $options = array(
                    self::PARAM_ROLE => self::PARAM_BUTTON
                    , self::PARAM_HREF => $this->datalink
                );
                break;
            case self::TAG_INPUT:
                $options = array(
                    self::PARAM_TYPE => self::PARAM_BUTTON
                    , self::PARAM_VALUE => $this->title
                );
                break;
        }
        $options[self::PARAM_ID] = $this->id;
        $options[self::PARAM_CLASS] = $this->class;
        $options[self::PARAM_STYLE] = $this->style;
        $this->content = (string) new \Pimvc\Html\Element\Decorator(
            $this->tag,
            $this->title,
            $options
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
     * setClass
     *
     */
    private function setClass()
    {
        $this->appendClass(self::CLASS_BUTTON, false);
        $this->appendClass($this->type);
        $this->appendClass($this->block);
        $this->appendClass($this->size);
        $this->appendClass($this->active);
        $this->appendClass($this->disabled);
        $this->appendClass($this->extraClass);
    }

    /**
     * appendClass
     *
     * @param string $class
     * @param boolean $withSpace
     */
    private function appendClass($class, $withSpace = true)
    {
        if ($class) {
            $space = ($withSpace) ? ' ' : '';
            $this->class .= $space . $class;
        }
    }
}
