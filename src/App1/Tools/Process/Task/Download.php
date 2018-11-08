<?php

/**
 * Description of Download
 *
 * @author pierrefromager
 */

namespace App1\Tools\Process\Task;

class Download
{
    const DL_ERRORS = 'errors';

    public $method;
    public $url;
    public $filename;

    public function run($args)
    {
        $params = [];
        $props = get_object_vars($this);
        unset($props[self::DL_ERRORS]);
        if ($args && $args !== false) {
            $params = @unserialize($args);
            $delta = array_diff_key($props, $params);
            if ($delta) {
                $msg = ' error missing params : ' . implode(',', array_keys($delta));
                $this->setError(0, $msg);
            } else {
                foreach ($params as $key => $value) {
                    $this->{$key} = $value;
                }
                $this->asyncDl();
            }
        } else {
            $this->setError(0, ' error no params.');
        }
        return [__CLASS__, $params];
    }

    private function asyncDl()
    {
        $client = new \GuzzleHttp\Client();
        $request = new \GuzzleHttp\Psr7\Request($this->method, $this->url);
        $st = [];
        $promise = $client->sendAsync(
            $request,
            [
                    //\GuzzleHttp\RequestOptions::SYNCHRONOUS => false,
                    \GuzzleHttp\RequestOptions::SINK => $this->filename,
                    \GuzzleHttp\RequestOptions::PROGRESS => function ($dt, $db, $ut, $ub) use (&$st) {
                        $pct = 1 + intval((1 + $db * 100) / ($dt + 1));
                        $sts = count($st);
                        $lv = ($st) ? $st[$sts - 1] : 1;
                        if (!in_array($pct, $st) && $lv < $pct) {
                            array_push($st, $pct);
                            sort($st);
                            $this->log('progress', [time(), $pct]);
                        }
                    }
                        ]
        )->then(function ($response) {
            //echo 'I completed! '; // . $response->getBody();
            return $response;
        }, function (\Exception $e) {
            $this->setError($e->getCode(), $e->getMessage());
        });
        $promise->wait();
    }

    private function progressDl()
    {
    }

    /**
     * log
     *
     */
    private function log($key, $payload)
    {
        \Pimvc\Logger::getFileInstance(
            \Pimvc\App::getInstance()->getPath() . '/cache/log/',
            \Pimvc\Logger::DEBUG,
            \Pimvc\Logger::LOG_ADAPTER_FILE
        )->logInfo($key, serialize($payload));
    }

    private function setError($code, $message)
    {
        throw new \Exception($message, $code);
    }
}
