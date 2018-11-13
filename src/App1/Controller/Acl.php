<?php
/**
 * App1\Controller\Acl
 *
 * is a controller for acls management.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */
namespace App1\Controller;

use \Pimvc\Tools\Acl as aclTools;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Tools\Session as sessionTools;
use \Pimvc\Views\Helpers\Acl as aclHelper;
use \Pimvc\Controller\Basic as basicController;
use Pimvc\Views\Helpers\Fa as faHelper;
use \App1\Helper\Lang\IEntries as ILang;

class Acl extends basicController
{

    use \App1\Helper\Reuse\Controller;

    const ACL_FORBIDEN = 'Accés non autorisé.';
    const LAYOUT_NAME = 'responsive';
    const _TITLE = 'title';
    const _ICON = 'icon';
    const _LINK = 'link';
    const _ITEMS = 'items';
    const _TEXT = 'text';

    protected $controllerPath = '';
    protected $controllerFileList = array();
    protected $ressources = array();
    protected $controllerActionList = array();
    protected $roles = array();
    protected $aclFilename = '';
    protected $aclTools = null;
    private $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()->getInstance()->getRequest()->getBaseUrl();
        $this->aclTools = new aclTools($reset = false, $xmlMode = true);
        $this->ressources = $this->aclTools->getRessources();
    }

    /**
     * manage
     *
     * @return array
     */
    final public function manage()
    {
        cssCollection::add('/public/css/acl.css');
        cssCollection::save();
        $params = self::ACL_FORBIDEN;
        if (sessionTools::isAdmin()) {
            $aclHelper = (new aclHelper($this->ressources))
                ->setTitle('Gestion des droits')
                ->render();
            $cr = '<br style="clear:both"/>';
            $params = (string) $aclHelper . $cr;
            unset($aclHelper);
        }
        if ($errors = $this->aclTools->getErrors()) {
            $params = $errors[0];
        }
        return (string) $this->getLayout((string) $params);
    }

    /**
     * toggle
     *
     * @return array
     */
    final public function toggle()
    {
        $id = $this->getParams('id');
        $content = self::ACL_FORBIDEN;
        if (sessionTools::isAdmin() && $id) {
            list($ctrl, $action, $role) = explode('-', $id);
            $controller = str_replace('_', '\\', $ctrl);
            $acl = $this->aclTools->get($controller, $action, $role);
            $this->aclTools->set($controller, $action, $role, $this->toggleAcl($acl));
            $acl = $this->aclTools->get($controller, $action, $role);
            $jsonAclParams = [
                'acl_enable' => $acl
                , 'acl_disable' => $this->toggleAcl($acl)
                , 'success' => $this->isValid($acl)
            ];
            return $this->getJsonResponse($jsonAclParams);
        }
        return array('content' => $content);
    }

    /**
     * reload acl schema.
     *
     */
    private function reload()
    {
        $this->ressources = $this->aclTools->getRessources();
    }

    /**
     * toggleAcl return opposite acl
     *
     * @param string $acl
     * @return string
     */
    private function toggleAcl($acl)
    {
        return ($acl == aclTools::ACL_ALLOW) ? aclTools::ACL_DENY : aclTools::ACL_ALLOW;
    }

    /**
     * isValid return true if acl is valid
     *
     * @param string $acl
     * @return boolean
     */
    private function isValid($acl)
    {
        return in_array(
            $acl, [aclTools::ACL_ALLOW, aclTools::ACL_DENY]
        );
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
     * getNavConfig
     *
     * @return array
     */
    private function getNavConfig()
    {
        return [
            self::_TITLE => [
                self::_TEXT => $this->translate(ILang::__HOME),
                self::_ICON => faHelper::getFontClass(faHelper::HOME),
                self::_LINK => $this->baseUrl
            ],
            self::_ITEMS => []
        ];
    }
}
