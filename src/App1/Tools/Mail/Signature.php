<?php

/**
 * App1\Tools\Mail\Signature
 */

namespace App1\Tools\Mail;

class Signature
{

    const _R = "\r";
    const _N = "\n";
    const _T = "\t";
    const _RN = "\r\n";
    const _RNT = "\r\n\t";
    const _RNRN = "\r\n\r\n";
    const _M_WSP_REG = '/\s+/';
    const _DELIM = ':';
    const _SEP = ';';
    const _OPENSSL = 'openssl';
    const _ERROR_SSL = 'OpenSSL extension not installed';
    const _UTF_8 = 'UTF-8';
    const _SIMPLE = 'simple';
    const _SIGNED_HEADERS = 'signed_headers';
    const _USE_DKIM = 'use_dkim';
    const _USE_DOMAIN_KEYS = 'use_domainKeys';
    const _IDENTITY = 'identity';
    const _DK_CANONICALIZATION = 'dk_canonicalization';
    const _DKIM_BODY_CANONICALIZATION = 'dkim_body_canonicalization';
    const _DKIM_SIGNATURE = 'dkim-signature';
    const _NOFWS = 'nofws';
    const _RELAXED = 'relaxed';
    const _ERROR_NO_HEADERS = 'No headers found to sign the e-mail with !';
    const _ERROR_DKIM_CANT_SIGN = 'Could not sign e-mail with DKIM : %s';

    private $privateKey;
    private $domain;
    private $selector;
    private $options;
    private $canonicalizedHeadersRelaxed;

    /**
     * __construct
     *
     * @param string $privateKey
     * @param string $passphrase
     * @param string $domain
     * @param string $selector
     * @param array $options
     */
    public function __construct($privateKey, $passphrase, $domain, $selector, $options = [])
    {

        if (!extension_loaded(self::_OPENSSL)) {
            throw new \Exception(self::_ERROR_SSL);
        }

        $this->privateKey = openssl_get_privatekey($privateKey, $passphrase);
        $this->domain = $domain;
        $this->selector = $selector;

        $defaultOptions = $this->getDefaultOptions();
        if (isset($options[self::_SIGNED_HEADERS])) {
            // lower case fields
            foreach ($options[self::_SIGNED_HEADERS] as $key => $value) {
                $options[self::_SIGNED_HEADERS][$key] = strtolower($value);
            }

            // delete the default fields if a custom list is provided, not merge
            $defaultOptions[self::_SIGNED_HEADERS] = [];
        }

        $this->options = array_replace_recursive($defaultOptions, $options);
        return $this;
    }

    /**
     * getSignedHeaders
     *
     * You may leave $to and $subject empty if the corresponding headers are already
     * in $headers
     *
     * To and Subject are not supposed to be present in $headers if you
     * use the php mail() function, because it takes care of that itself in
     * parameters for security reasons, so we reconstruct them here for the
     * signature only
     *
     * @param string $to
     * @param string $subject
     * @param string $body
     * @param string $headers
     * @return string
     */
    public function getSignedHeaders($to, $subject, $body, $headers)
    {

        $signedHeaders = '';

        if (!empty($to) || !empty($subject)) {
            $headers .= (mb_substr($headers, mb_strlen($headers, self::_UTF_8) - 2, 2, self::_UTF_8) == self::_RN) ?
                    '' :
                    self::_RN;

            if (!empty($to)) {
                $headers .= 'To: ' . $to . self::_RN;
            }

            if (!empty($subject)) {
                $headers .= 'Subject: ' . $subject . self::_RN;
            }
        }

        // get the clean version of headers used for signature
        $this->canonicalizedHeadersRelaxed = $this->dkimCanonicalizeHeadersRelaxed($headers);

        if (!empty($this->canonicalizedHeadersRelaxed)) {
            // Domain Keys must be the first header, it is an RFC (stupid) requirement
            if ($this->options[self::_USE_DOMAIN_KEYS] == true) {
                $signedHeaders .= $this->getDkHeader($body, $headers);
            }

            if ($this->options[self::_USE_DKIM] == true) {
                $signedHeaders .= $this->getDkimHeader($body);
            }
        } else {
            trigger_error(self::_ERROR_NO_HEADERS, E_USER_WARNING);
        }

        return $signedHeaders;
    }

    /**
     * getDefaultOptions
     *
     * This function will not let you ask for the simple header canonicalization because
     * it would require more code, it would not be more secure and mails would yet be
     * more likely to be rejected : no point in that
     *
     * @return array
     */
    private function getDefaultOptions()
    {
        return [
            self::_USE_DKIM => true,
            // disabled by default, see why at the top of this file
            self::_USE_DOMAIN_KEYS => false,
            /*
             * Allowed user, defaults is "@<MAIL_DKIM_DOMAIN>", meaning anybody in the
             * MAIL_DKIM_DOMAIN domain. Ex: 'admin@mydomain.tld'. You'll never have to use
             * this unless you do not control the "From" value in the e-mails you send.
             */
            self::_IDENTITY => null,
            // "relaxed" is recommended over "simple" for better chances of success
            self::_DKIM_BODY_CANONICALIZATION => self::_RELAXED,
            // "nofws" is recommended over "simple" for better chances of success
            self::_DK_CANONICALIZATION => self::_NOFWS,
            /*
             * The default list of headers types you want to base the signature on. The
             * types here (in the default options) are to be put in lower case, but the
             * types in $options can have capital letters. If one or more of the headers
             * specified are not found in the $headers given to the function, they will
             * just not be used.
             * If you supply a new list, it will replace the default one
             */
            self::_SIGNED_HEADERS => [
                'mime-version',
                'from',
                'to',
                'subject',
                'reply-to'
            ]
        ];
    }

    /**
     * dkimCanonicalizeHeadersRelaxed
     *
     * This function returns an array of relaxed canonicalized headers (lowercases the
     * header type and cleans the new lines/spaces according to the RFC requirements).
     * only headers required for signature (specified by $options) will be returned
     * the result is an array of the type : array(headerType => fullHeader [, ...]),
     * e.g. array('mime-version' => 'mime-version:1.0')
     *
     * @param string $sHeaders
     * @return array
     */
    private function dkimCanonicalizeHeadersRelaxed($sHeaders)
    {

        $aHeaders = [];

        // a header value which is spread over several lines must be 1-lined
        $sHeaders = preg_replace("/\n\s+/", " ", $sHeaders);

        $lines = explode(self::_RN, $sHeaders);

        foreach ($lines as $key => $line) {
            // delete multiple WSP

            $line = preg_replace(self::_M_WSP_REG, ' ', $line);
            //var_dump($line);die;
            if (!empty($line)) {
                // header type to lowercase and
                // delete WSP which are not part of the header value
                $line = explode(self::_DELIM, $line, 2);

                $headerType = trim(strtolower($line[0]));
                $headerValue = trim($line[1]);
                $isDkimSignature = ($headerType == self::_DKIM_SIGNATURE);

                if (in_array($headerType, $this->options[self::_SIGNED_HEADERS]) || $isDkimSignature) {
                    $aHeaders[$headerType] = $headerType . self::_DELIM . $headerValue;
                }
            }
        }

        return $aHeaders;
    }

    /**
     * dkimCanonicalizeBodySimple
     *
     * Apply RFC 4871 requirements before body signature.
     *
     * Unlike other libraries, we do not convert all \n in the body to \r\n here
     * because the RFC does not specify to do it here. However it should be done
     * anyway since MTA may modify them and we recommend you do this on the mail
     * body before calling this DKIM class - or signature could fail.
     *
     * @param string $body
     * @return string
     */
    private function dkimCanonicalizeBodySimple($body)
    {

        // remove multiple trailing CRLF
        while (mb_substr($body, mb_strlen($body, self::_UTF_8) - 4, 4, self::_UTF_8) == self::_RNRN) {
            $body = mb_substr($body, 0, mb_strlen($body, self::_UTF_8) - 2, self::_UTF_8);
        }

        // must end with CRLF anyway
        if (mb_substr($body, mb_strlen($body, self::_UTF_8) - 2, 2, self::_UTF_8) != self::_RN) {
            $body .= self::_RN;
        }

        return $body;
    }

    /**
     * dkimCanonicalizeBodyRelaxed
     *
     * Apply RFC 4871 requirements before body signature
     *
     * @param string $body
     * @return string
     */
    private function dkimCanonicalizeBodyRelaxed($body)
    {

        $lines = explode(self::_RN, $body);

        foreach ($lines as $key => $value) {
            // ignore WSP at the end of lines
            $value = rtrim($value);

            // ignore multiple WSP inside the line
            $lines[$key] = preg_replace(self::_M_WSP_REG, ' ', $value);
        }

        // ignore empty lines at the end
        return $this->dkimCanonicalizeBodySimple(implode(self::_RN, $lines));
    }

    /**
     * dkCanonicalizeSimple
     *
     * Apply RFC 4870 requirements before body signature.
     * Note : the RFC assumes all lines end with CRLF, and we assume you already
     * took care of that before calling the class
     *
     * @param string $body
     * @param string $sHeaders
     * @return string
     */
    private function dkCanonicalizeSimple($body, $sHeaders)
    {

        // keep only headers wich are in the signature headers
        $aHeaders = explode(self::_RN, $sHeaders);
        foreach ($aHeaders as $key => $line) {
            if (!empty($aHeaders)) {
                // make sure this line is the line of a new header and not the
                // continuation of another one
                $c = substr($line, 0, 1);
                $isSignedHeader = true;

                // new header
                if (!in_array($c, [self::_R, self::_N, self::_T, ' '])) {
                    $h = explode(self::_DELIM, $line);
                    $header_type = strtolower(trim($h[0]));

                    // keep only signature headers
                    if (in_array($header_type, $this->options[self::_SIGNED_HEADERS])) {
                        $isSignedHeader = true;
                    } else {
                        unset($aHeaders[$key]);
                        $isSignedHeader = false;
                    }
                }
                // continuated header
                else {
                    // do not keep if it belongs to an unwanted header
                    if ($isSignedHeader == false) {
                        unset($aHeaders[$key]);
                    }
                }
            } else {
                unset($aHeaders[$key]);
            }
        }
        $mail = implode(self::_RN, $aHeaders) . self::_RNRN . $body . self::_RN;

        // remove all trailing CRLF
        while (mb_substr($body, mb_strlen($mail, self::_UTF_8) - 4, 4, self::_UTF_8) == self::_RNRN) {
            $mail = mb_substr($mail, 0, mb_strlen($mail, self::_UTF_8) - 2, self::_UTF_8);
        }

        return $mail;
    }

    /**
     * dkCanonicalizeNofws
     *
     * Apply RFC 4870 requirements before body signature. Do not modify
     *
     * @param string $body
     * @param array $sHeaders
     * @return type
     */
    private function dkCanonicalizeNofws($body, $sHeaders)
    {

        $aHeaders = explode(self::_RN, $this->toOneLine($sHeaders));

        foreach ($aHeaders as $key => $line) {
            if (!empty($line)) {
                $h = explode(self::_DELIM, $line);
                $header_type = strtolower(trim($h[0]));

                // keep only signature headers
                if (in_array($header_type, $this->options[self::_SIGNED_HEADERS])) {
                    // delete all WSP in each line
                    $aHeaders[$key] = $this->removeWsp($line);
                } else {
                    unset($aHeaders[$key]);
                }
            } else {
                unset($aHeaders[$key]);
            }
        }
        $sHeaders = implode(self::_RN, $aHeaders);

        // delete all WSP in each body line
        $bodyLines = explode(self::_RN, $body);

        foreach ($bodyLines as $key => $line) {
            $bodyLines[$key] = $this->removeWsp($line);
        }

        $body = rtrim(implode(self::_RN, $bodyLines)) . self::_RN;

        return $sHeaders . self::_RNRN . $body;
    }

    /**
     * removeWsp
     *
     * @param string $line
     * @return string
     */
    private function removeWsp($line)
    {
        return preg_replace("/\s/", '', $line);
    }

    /**
     * toOneLine
     *
     * Ex : a header value which is spread over several lines must be 1-lined
     *
     * @param string $string
     * @return string
     */
    private function toOneLine($string)
    {
        return preg_replace("/\r\n\s+/", ' ', $string);
    }

    /**
     * getDkimHeader
     *
     * The function will return no DKIM header (no signature) if there is a failure,
     * so the mail will still be sent in the default unsigned way
     * it is highly recommended that all linefeeds in the $body and $headers you submit
     * are in the CRLF (\r\n) format !! Otherwise signature may fail with some MTAs
     *
     * @param string $body
     * @return string
     */
    private function getDkimHeader($body)
    {

        $body = ($this->options[self::_DKIM_BODY_CANONICALIZATION] == self::_SIMPLE) ?
                $this->dkimCanonicalizeBodySimple($body) :
                $this->dkimCanonicalizeBodyRelaxed($body);

        $dkimHeader = 'DKIM-Signature: ' .
                'v=1;' . self::_RNT .
                'a=rsa-sha256;' . self::_RNT .
                'q=dns/txt;' . self::_RNT .
                's=' . $this->selector . self::_SEP . self::_RNT .
                't=' . time() . self::_SEP . self::_RNT .
                'c=relaxed/' . $this->options[self::_DKIM_BODY_CANONICALIZATION] . self::_SEP . self::_RNT .
                'h=' . implode(self::_DELIM, array_keys($this->canonicalizedHeadersRelaxed)) . self::_SEP . self::_RNT .
                'd=' . $this->domain . self::_SEP . self::_RNT .
                $this->getIdentity() .
                'bh=' . $this->getBh($body) . self::_SEP . self::_RNT .
                'b=';

        // now for the signature we need the canonicalized version of the $dkimHeader
        // we've just made
        $canonicalizedDkimHeader = $this->dkimCanonicalizeHeadersRelaxed($dkimHeader);

        // we sign the canonicalized signature headers
        $toBeSigned = implode(self::_RN, $this->canonicalizedHeadersRelaxed)
                . self::_RN . $canonicalizedDkimHeader[self::_DKIM_SIGNATURE];

        $signature = ''; // $signature is sent by reference in openssl_sign
        if (openssl_sign($toBeSigned, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            $dkimHeader .= rtrim(chunk_split(base64_encode($signature), 64, self::_RNT)) . self::_RN;
        } else {
            trigger_error(
                sprintf(self::_ERROR_DKIM_CANT_SIGN, $toBeSigned),
                E_USER_WARNING
            );
            $dkimHeader = '';
        }

        return $dkimHeader;
    }

    /**
     * getBh
     *
     * Base64 of packed binary SHA-1 hash of body
     *
     * @param string $body
     * @return string
     */
    private function getBh($body)
    {
        return rtrim(chunk_split(base64_encode(pack("H*", sha1($body))), 64, self::_RNT));
    }

    /**
     * getIdentity
     *
     * @return string
     */
    private function getIdentity()
    {
        return ($this->options[self::_IDENTITY] == null) ?
                '' :
                ' i=' . $this->options[self::_IDENTITY] . self::_SEP . self::_RNT;
    }

    /**
     * getDkHeader
     *
     * Creating DomainKey-Signature
     * we signed the canonicalized signature headers + the canonicalized body
     *
     * @param string $body
     * @param string $sHeaders
     * @return string
     */
    private function getDkHeader($body, $sHeaders)
    {

        $domainkeysHeader = 'DomainKey-Signature: ' .
                'a=rsa-sha256; ' . self::_RNT .
                'c=' . $this->options[self::_DK_CANONICALIZATION] . '; ' . self::_RNT .
                'd=' . $this->domain . '; ' . self::_RNT .
                's=' . $this->selector . '; ' . self::_RNT .
                'h=' . implode(self::_DELIM, array_keys($this->canonicalizedHeadersRelaxed)) . '; ' . self::_RNT .
                'b=';

        $toBeSigned = ($this->options[self::_DK_CANONICALIZATION] == self::_SIMPLE) ?
                $this->dkCanonicalizeSimple($body, $sHeaders) :
                $this->dkCanonicalizeNofws($body, $sHeaders);

        $signature = '';
        if (openssl_sign($toBeSigned, $signature, $this->privateKey, OPENSSL_ALGO_SHA256)) {
            $domainkeysHeader .= rtrim(chunk_split(base64_encode($signature), 64, self::_RNT)) . self::_RN;
        } else {
            $domainkeysHeader = '';
        }

        return $domainkeysHeader;
    }
}
