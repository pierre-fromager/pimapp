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

use \App1\Helper\Lang\IEntries as ILang;
use \App1\Helper\Controller\Acl as aclHelperController;

class Acl extends aclHelperController
{

    /**
     * manage
     *
     * @return \Pimvc\Http\Response
     */
    final public function manage()
    {
        $aclHelper = (new \Pimvc\Views\Helpers\Acl($this->ressources))
            ->setTitle($this->translate(ILang::__ACL_MANAGER))
            ->render();
        return (string) $this->getLayout(
            (string) $aclHelper . '<br style="clear:both"/>'
        );
    }

    /**
     * toggle
     *
     * @return \Pimvc\Http\Response
     */
    final public function toggle()
    {
        if ($id = $this->getParams(self::_ID)) {
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
    }

    /**
     * policies

     * @return \Pimvc\Http\Response
     */
    final public function policies()
    {
        return $this->getJsonResponse($this->getAcls());
    }
}
