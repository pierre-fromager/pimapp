<?php

/**
 * App1\Helper\Controller\Acl
 *
 * is a controller for acls management.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */

namespace App1\Helper\Controller;

use \Pimvc\Tools\Acl as aclTools;
use \Pimvc\Views\Helpers\Collection\Css as cssCollection;
use \Pimvc\Controller\Basic as basicController;
use \App1\Helper\Nav\Auto\Config as autoNavConfig;

class Acl extends basicController
{

    use \App1\Helper\Reuse\Controller;

    const LAYOUT_NAME = 'responsive';
    const _ID = 'id';

    protected $controllerPath = '';
    protected $controllerFileList = array();
    protected $ressources = array();
    protected $controllerActionList = array();
    protected $roles = array();
    protected $aclFilename = '';
    protected $aclTools = null;
    protected $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->baseUrl = $this->getApp()->getInstance()->getRequest()->getBaseUrl();
        $this->setAssets();
        $this->aclTools = new aclTools($reset = false, $xmlMode = true);
        $this->reload();
    }

    /**
     * reload acl schema.
     *
     */
    protected function reload()
    {
        $this->ressources = $this->aclTools->getRessources();
    }

    /**
     * toggleAcl return opposite acl
     *
     * @param string $acl
     * @return string
     */
    protected function toggleAcl($acl)
    {
        return ($acl == aclTools::ACL_ALLOW) ? aclTools::ACL_DENY : aclTools::ACL_ALLOW;
    }

    /**
     * isValid return true if acl is valid
     *
     * @param string $acl
     * @return boolean
     */
    protected function isValid($acl)
    {
        return in_array(
            $acl,
            [aclTools::ACL_ALLOW, aclTools::ACL_DENY]
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

    /*
     * getNavConfig
     *
     * @return array
     */

    protected function getNavConfig(): array
    {
        $filter = [
            '(user.*)\/(.*)(ge|il|it|rd|er)$',
            '(acl.*)\/(.*)(ge)$',
        ];
        return (new autoNavConfig)->setFilter($filter)->render()->getConfig();
    }

    /**
     * setAssets
     *
     */
    protected function setAssets()
    {
        cssCollection::add('/public/css/acl.css');
        cssCollection::save();
    }
}
