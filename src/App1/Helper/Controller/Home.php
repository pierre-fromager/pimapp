<?php
/**
 * Description of App1\Helper\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Helper\Controller;

use \Pimvc\Controller\Basic as basicController;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Views\Helpers\Collection\Js as jsCollection;
use \App1\Helper\Nav\Auto\Config as autoNavConfig;
use \App1\Helper\Lang\IEntries as ILang;

class Home extends basicController implements ILang
{

    use \App1\Helper\Reuse\Controller;

    const PARAM_HTML = 'html';
    const PARAM_NAV = 'nav';
    const PARAM_CAROUSEL = 'carousel';
    const LAYOUT_NAME = 'responsive';
    const VIEW_INDEX = '/Views/Home/Index.php';

    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->initAssets();
    }

    /**
     * initAssets
     *
     */
    private function initAssets()
    {
        $cssPath = '/public/css/';
        $cssAssets = [];
        for ($c = 0; $c < count($cssAssets); $c++) {
            cssCollection::add($cssPath . $cssAssets[$c]);
        }
        cssCollection::save();
        /*
          $jsPath = '/public/js/';
          $jsAssets = [
          '/gsap/plugins/CSSPlugin.min.js',
          '/gsap/easing/EasePack.min.js',
          '/gsap/TweenLite.min.js',
          '/gsap/jquery.gsap.min.js',
          ];
          for ($c = 0; $c < count($jsAssets); $c++) {
          jsCollection::add($jsPath . $jsAssets[$c]);
          } */
        jsCollection::save();
    }

    /**
     * getNavConfig
     *
     * @return array
     */
    protected function getNavConfig(): array
    {
        return (new autoNavConfig($this->getCalledNamespace()))
                ->setFilter(['ge$', 'lost', 'register', '!ort$'])
                ->render()
                ->getConfig();
    }
}
