<?php
namespace App1\Middleware;

class Before implements \Pimvc\Http\Interfaces\Layer
{
    public function peel($object, \Closure $next)
    {
        $app = \Pimvc\App::getInstance();
        $params = $app->getRequest()->getQueryTupple();
        if ((isset($params['token']))) {
            $dbConfig = $app->getConfig()->getSettings('dbPool');
            $authModel = new \App1\Model\Users($dbConfig);
            $tokenable = $authModel->getAuthByToken($params['token']);
            if ($tokenable) {
                //\Pimvc\Tools\User\Auth
                var_dump($tokenable);
                die;
            }
        }
        return $next($object);
    }
}
