<?php

namespace App1\Tools\Mail;

use App1\Tools\Mail\Signature as dkimSigner;

class Dkim implements \App1\Tools\Mail\IDkim
{

    private $domain;
    private $passphrase;
    private $selector;
    private $privateKey;
    private $mailerConfig;

    /**
     * __construct
     *
     * @param array $mailerConfig
     * @return $this
     */
    public function __construct($mailerConfig = [])
    {
        $this->mailerConfig = $mailerConfig;
        $this->init();
        return $this;
    }

    /**
     * shouldSign
     *
     * @return bool
     */
    public function shouldSign()
    {
        return (!empty($this->domain) && !empty($this->selector) && !empty($this->privateKey));
    }

    /**
     * getSigner
     *
     * @return dkimSigner
     */
    public function getSigner()
    {
        return new dkimSigner(
            $this->privateKey,
            $this->passphrase,
            $this->domain,
            $this->selector,
            []
        );
    }

    /**
     * init
     *
     */
    private function init()
    {
        if (isset($this->mailerConfig[self::_DKIM])) {
            $dkimConfig = $this->mailerConfig[self::_DKIM];
            $this->passphrase = isset($dkimConfig[self::_PASSPHRASE]) ? $dkimConfig[self::_PASSPHRASE] : '';
            $this->domain = isset($dkimConfig[self::_DOMAIN]) ? $dkimConfig[self::_DOMAIN] : '';
            $this->selector = isset($dkimConfig[self::_SELECTOR]) ? $dkimConfig[self::_SELECTOR] : '';
            $this->privateKey = isset($dkimConfig[self::_PRIVATE_KEY]) ? $dkimConfig[self::_PRIVATE_KEY] : '';
        }
    }
}
