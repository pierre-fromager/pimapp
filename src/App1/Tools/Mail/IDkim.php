<?php

namespace App1\Tools\Mail;

interface IDkim
{

    const _DKIM = 'dkim';
    const _PASSPHRASE = 'passphrase';
    const _DOMAIN = 'domain';
    const _SELECTOR = 'selector';
    const _PRIVATE_KEY = 'privateKey';
    const _DKIM_SIGNATURE_HEADER = 'DKIM-Signature';

    public function shouldSign();

    public function getSigner();
}
