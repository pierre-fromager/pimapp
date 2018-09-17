<?php
namespace App1\Middleware;

class After implements \Pimvc\Http\Interfaces\Layer
{
    public function peel($object, \Closure $next)
    {
        $response = $next($object);
        //$object->runs[] = 'after';
        return $response;
    }
}
