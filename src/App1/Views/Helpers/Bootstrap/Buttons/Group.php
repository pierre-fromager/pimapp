<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Buttons\Group
 *
 * @author pierrefromager
 */
namespace App1\Views\Helpers\Bootstrap\Buttons;

class Group
{
    const PARAM_CLASS = 'class';
    const PARAM_ID = 'id';
    const BUTTONS_GROUP_CLASS = 'btn-group';
    const BUTTONS_GROUP_CLASS_VERTICAL = 'btn-group-vertical';
    
    protected $content;
    
    /**
     * __construct
     *
     */
    public function __construct()
    {
        $this->content = '';
    }
    
    /**
     * render
     *
     */
    public function render()
    {
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
}
