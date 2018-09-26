<?php
/**
 * App1\Tools\Mail\Sender
 *
 * is a tool to send smtp message through Nette\Mail
 */
namespace App1\Tools\Mail;

use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use App1\Tools\Mail\Dkim;

class Sender
{

    private $message;
    private $smtpMailerConfig;
    private $smtpMailer;
    private $from;
    private $to;
    private $cc;
    private $subject;
    private $body;
    private $error;
    private $errorCode;
    private $errorMessage;

    /**
     * __construct
     *
     * @return $this
     */
    public function __construct()
    {
        $this->init();
        return $this;
    }

    /**
     * setFrom
     *
     * @param string $from
     * @return $this
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * setTo
     *
     * @param string $to
     * @return $this
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * setCc
     *
     * @param string $cc
     * @return $this
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * setSubject
     *
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * setBody
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * send
     *
     * @param bool $asHtml
     * @return $this
     */
    public function send($asHtml = true)
    {
        $this->resetError();
        $this->message = new Message;
        try {
            $this->message->setFrom($this->from, $this->from);
            $this->message->addReplyTo($this->from, $this->from);
            $this->message->addTo($this->to, $this->to);
            if ($this->cc) {
                $this->message->addTo($this->cc, $this->cc);
            }
            $this->message->setSubject($this->subject);

            if ($asHtml) {
                $this->message->setHTMLBody($this->body);
            } else {
                $this->message->setBody($this->body);
            }

            $dkimTool = (new Dkim($this->smtpMailerConfig));
            if ($dkimTool->shouldSign()) {
                $initialHeaders = $this->message->getHeaders();
                $canonizedHeaders = $this->getCanonizedHeaders($initialHeaders);
                $signedHeaders = $dkimTool->getSigner()->getSignedHeaders(
                    $this->to, $this->message->getSubject(), $this->message->getBody(), $canonizedHeaders, $this->signerOptions()
                );
                $signContent = substr($signedHeaders, 15);
                $this->message->setHeader(Dkim::_DKIM_SIGNATURE_HEADER, $signContent);
            }
            try {
                $this->smtpMailer->send($this->message);
            } catch (\Exception $exc) {
                $this->setError($exc->getCode(), $exc->getMessage());
            }
        } catch (\Exception $exc) {
            $this->setError($exc->getCode(), $exc->getMessage());
        }
        return $this;
    }

    /**
     * isError
     *
     * @return bool
     */
    public function isError()
    {
        return $this->errorCode > 0;
    }

    /**
     * getError
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * getCanonizedHeaders
     *
     * @return string
     */
    private function getCanonizedHeaders($headers)
    {
        $sHeader = '';
        $fromTo = ['from', 'to', 'reply-to'];
        foreach ($headers as $key => $value) {
            $v = $value;
            if (is_array($value)) {
                if (in_array(strtolower($key), $fromTo)) {
                    $kkeys = array_keys($value);
                    $mainKey = $kkeys[0];
                    $v = '"' . $value[$mainKey] . '" <' . $mainKey . '>';
                }
            }
            $sHeader .= $key . ': ' . $v . "\r\n";
        }
        return $sHeader;
    }

    /**
     * signerOptions
     *
     * @return array
     */
    private function signerOptions()
    {
        return [
            'use_domainKeys' => true,
            'use_dkim' => true,
            'signed_headers' => [
                'message-Id',
                'Content-type',
                'To',
                'subject'
            ]
        ];
    }

    /**
     * init
     *
     */
    private function init()
    {
        $this->setSmtpConfig();
        $this->setSmtpMailer();
    }

    /**
     * setSmtpConfig
     *
     */
    private function setSmtpConfig()
    {
        $config = \Pimvc\App::getInstance()->getConfig();
        $this->smtpMailerConfig = $config->getSettings('app')['mailer'];
    }

    /**
     * setSmtpMailer
     *
     */
    private function setSmtpMailer()
    {
        $this->smtpMailer = new SmtpMailer($this->smtpMailerConfig);
    }

    /**
     * setError
     *
     * @param int $erroCode
     * @param string $errorMessage
     */
    private function setError($erroCode = 0, $errorMessage = '')
    {
        $this->error = ($erroCode != 0);
        $this->errorCode = $erroCode;
        $this->errorMessage = $errorMessage;
    }

    /**
     * resetError
     *
     */
    private function resetError()
    {
        $this->error = false;
        $this->errorCode = 0;
        $this->errorMessage = '';
    }
}
