<?php

/**
 * Description of App1\Views\Helpers\Bootstrap\Modal
 *
 * @author pierrefromager
 */

namespace App1\Views\Helpers\Bootstrap;

class Modal
{
    const TEMPLATE_NAME = '/Template/Modal.php';

    protected $id = 'zeModal';
    protected $title = '';
    protected $body = '';
    protected $url;
    protected $content;
    protected $isExternal = false;
    protected static $counter = 0;

    /**
     * __construct
     *
     * @param string $title
     * @param string $body
     * @param string $url
     */
    public function __construct($title, $body = '', $url = '')
    {
        $this->setTitle($title);
        $this->setBody($body);
        $this->setUrl($url);
    }
    
    /**
     * setId
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * setTitle
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * setBody
     *
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }
    
    /**
     * setUrl
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        $this->isExternal = (!empty($url));
    }
    
    /**
     * render
     *
     * @return string
     */
    public function render()
    {
        $params = array(
            'modal_id' => $this->id
            , 'modal_title' => $this->title
            , 'modal_content' => ($this->isExternal)
                ? file_get_contents($this->url, true)
                : $this->body
        );
        $this->content = (string) (new \Pimvc\View())
                        ->setParams($params)
                        ->setFilename(__DIR__ . self::TEMPLATE_NAME);
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
}
