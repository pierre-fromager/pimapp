<?php
/**
 * Description of App1\Controller\Home
 *
 * @author Pierre Fromager
 */
namespace App1\Controller;

use App1\Helper\Controller\Home as HelperHomeController;
use \App1\Helper\Lang\IEntries as ILang;

class Home extends HelperHomeController implements ILang
{

    use \App1\Helper\Reuse\Controller;

    /**
     * index
     *
     * @return Response
     */
    final public function index()
    {
        $viewParams = [
            'mainTitle' => $this->translate(ILang::__WELCOME_TO)
            . ' Pimapp',
            'content' => $this->translate(ILang::__PROPULSED_BY)
            . ' <b>Pimvc</b>',
            'fwk' => 'Pimvc',
            'phpVersion' => PHP_VERSION
        ];
        return (string) $this->getLayout(
                (string) $this->getView($viewParams, self::VIEW_INDEX)
        );
    }
}
