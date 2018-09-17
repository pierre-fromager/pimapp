<?php

namespace App1\Middleware;

class Acl implements \Pimvc\Http\Interfaces\Layer
{

    const ACL_DEFAULT_CONTROLLER = 'home';
    const ACL_DEFAULT_ACTION = 'index';
    const ACL_TRANSFO = 'ucfirst';
    const ACL_BS = '\\';
    const ACL_SL = '/';
    const ACL_DEBUG = true;

    private $app;
    private $aclTools;
    private $controller;
    private $action;
    private $role;
    private $allowed;

    /**
     * peel
     *
     * @param mixed $object
     * @param \Closure $next
     * @return \Closure
     */
    public function peel($object, \Closure $next)
    {
        $this->process();
        return $next($object);
    }

    /**
     * process
     *
     */
    private function process()
    {
        $this->app = \Pimvc\App::getInstance();
        $this->aclTools = new \Pimvc\Tools\Acl();
        $this->setCAR();
        $this->allowed = $this->verify();
        if (!$this->allowed) {
            $this->app->getController()->setForbidden();
        }
        $this->log();
    }

    /**
     * verify
     *
     * @return boolean
     */
    private function verify()
    {
        return $this->aclTools->isAllowed(
            $this->controller,
            $this->action,
            $this->role
        );
    }

    /**
     * setCAR
     *
     */
    private function setCAR()
    {
        $this->role = \Pimvc\Tools\Session::getProfil();
        $prerouting = $this->app->getRouter()->compile();
        if (is_null($prerouting) || count($prerouting) === 1) {
            $prerouting[0] = self::ACL_DEFAULT_CONTROLLER;
            $prerouting[1] = self::ACL_DEFAULT_ACTION;
        }
        list($this->controller, $this->action) = $prerouting;
        unset($prerouting);
        $this->controller = $this->getNsControllerName($this->controller);
    }

    /**
     * getNsControllerName
     *
     * @param string $controller
     * @return string
     */
    private function getNsControllerName($controller)
    {
        $nsc = $this->aclTools->getNamespaceCtrlPrefix()
                . self::ACL_BS . str_replace(self::ACL_SL, self::ACL_BS, $controller);
        $nscparts = array_map(self::ACL_TRANSFO, explode(self::ACL_BS, $nsc));
        $nsc = implode(self::ACL_BS, $nscparts);
        return $nsc;
    }

    /**
     * log
     *
     */
    private function log()
    {
        if (self::ACL_DEBUG) {
            $this->app->getLogger()->log(
                __CLASS__,
                \Pimvc\Logger::DEBUG,
                [
                'controller' => $this->controller,
                'action' => $this->action,
                'role' => $this->role,
                'allow' => $this->allowed
                    ]
            );
        }
    }
}
