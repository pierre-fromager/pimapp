<?php
/**
 * App1\Views\Helpers\Pages\Paginator
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 */
namespace App1\Views\Helpers\Pages;

use Pimvc\Views\Helpers\Collection\Css as cssCollection;
use Pimvc\Views\Helpers\Collection\Js as jsCollection;

class Paginator
{
    const PAGINATOR_DEFAULT_PAGE = 0;
    const PAGINATOR_DEFAULT_SCALE = 11;
    const PAGINATOR_LABEL_BEGIN = 'Début';
    const PAGINATOR_LABEL_END = 'Fin';

    protected $content;
    protected $link;
    protected $curentPage;
    protected $pageSize;
    protected $maxPage;
    protected $baseUrl;

    /**
     * __construct paginator
     *
     * @param string $link (ex: controler/action)
     * @param int $curentPage
     * @param int $pageSize
     * @param int $maxPage
     */
    public function __construct($link, $curentPage, $pageSize, $maxPage)
    {
        $cssPath = 'public/css/';
        cssCollection::add($cssPath . 'paginator.css');
        cssCollection::save();
        $this->content = '';
        $this->baseUrl = \Pimvc\App::getInstance()->getRequest()->getBaseUrl();
        $this->link = $link . '/page/';
        $this->curentPage = $curentPage;
        $this->pageSize = $pageSize;
        $this->maxPage = $maxPage;
        if ($this->maxPage > 0) {
            $this->process();
        }
    }



    /**
     * __toString return paginator content as string
     *
     * @return type
     */
    public function __toString()
    {
        return '<div class="paginator">' . $this->content . '</div>';
    }

    /**
     * process set paginator content
     *
     */
    protected function process()
    {
        $this->content = $this->getNav();
    }

    /**
     * getNavItem
     *
     * @param string $label
     * @param int $pageNumber
     * @param string $link
     * @param string $class
     * @return string
     */
    private function getNavItem($label, $pageNumber, $link, $class)
    {
        return '<span class=" ' . $class . '">'
            . '<a class="paginatorItemLink" href="' . $this->baseUrl
            . $link . $pageNumber . '">'
            . $label . '</a></span>';
    }

    /**
     * getNav returns paginator navigation
     *
     * @return string
     */
    private function getNav()
    {
        $nav = $this->getNavItem(
            self::PAGINATOR_LABEL_BEGIN,
            self::PAGINATOR_DEFAULT_PAGE,
            $this->link,
            'paginatorItem begin'
        );
        $pageNumber = 0;
        $navScale = self::PAGINATOR_DEFAULT_SCALE - 1;
        $navSplit = $navScale / 2;
        if ($this->maxPage > 0) {
            $start = $this->curentPage - $navSplit;
            $compound = ($start < 0) ? abs($start) : 0;
            if ($compound) {
                $start = 0;
            }
            $stop = $this->curentPage + $navSplit + $compound;
            if ($stop > $this->maxPage) {
                $stop = $this->maxPage;
                $start = $stop - (self::PAGINATOR_DEFAULT_SCALE - 1);
            }
            if ($start < 0) {
                $start = 0;
            }
            for ($c = $start; $c < $stop; $c++) {
                $pageNumber = $c;
                $class = (($pageNumber == $this->curentPage))
                    ? 'paginatorItemSelected'
                    : 'paginatorItem';
                $nav .= $this->getNavItem(
                    $pageNumber + 1,
                    $pageNumber,
                    $this->link,
                    $class
                );
            }
        }
        $nav .= $this->getNavItem(
            self::PAGINATOR_LABEL_END,
            $this->maxPage -1,
            $this->link,
            'paginatorItem end'
        );
        return $nav;
    }
}
