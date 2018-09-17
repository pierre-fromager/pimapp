<?php
/**
 * Description of App1\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Controller;

use App1\Helper\Controller\Home as HelperHomeController;
use App1\Views\Helpers\Bootstrap\Nav as NavMenu;

class Home extends HelperHomeController
{
    /**
     * index
     *
     * @return Response
     */
    final public function index()
    {
        $nav = (new NavMenu())->setParams($this->getNavConfig())->render();
        $viewParams = [
            'nav' => (string) $nav,
            'mainTitle' => 'Welcome To <b>Pimapp</b>',
            'content' => 'propulsed by <b>Pimvc</b>'
        ];
        $view = $this->getView($viewParams, self::VIEW_INDEX);
        return $this->getHtmlResponse($this->getLayout((string) $view));
    }
}
