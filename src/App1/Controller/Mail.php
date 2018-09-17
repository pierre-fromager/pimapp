<?php
/**
 * class App1\Controller\Mail
 *
 * is a controller for mail management.
 *
 * @author Pierre Fromager <pf@pier-infor.fr>
 * @copyright Pier-Infor
 * @version 1.0
 */
namespace App1\Controller;

use Pimvc\Tools\Session as sessionTools;
use Pimvc\Html\Element\Decorator as mailDecorator;
use Pimvc\Input\Filter as inputFilter;
use Pimvc\Controller\Basic as basicController;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use \App1\Model\Users as modelUser;

class Mail extends basicController
{
    const DEBUG = false;
    const DEBUG_FROM = 'Cra Info <info@pier-infor.fr>';
    const DEBUG_TO = 'pf@pier-infor.fr';
    const DEBUG_CC = 'pier-infor@sfr.fr';
    const DEBUG_SUBJECT = 'Test - Mail - Sender';
    const DEBUG_BODY = 'Can you read this ?';
    const PARAM_APP = 'app';
    const PARAM_ID = 'id';
    const PARAM_MAILER = 'mailer';
    const PARAM_MONTH = 'month';
    const PARAM_YEAR = 'year';
    const PARAM_NAME = 'name';
    const PARAM_EMAIL = 'email';
    const PARAM_UID = 'uid';
    const PARAM_FROM = 'from';
    const PARAM_DMONTH = 'dmonth';
    const PARAM_BDAYS = 'bdays';
    const PARAM_TO = 'to';
    const PARAM_CC = 'cc';
    const PARAM_SUBJECT = 'subject';
    const PARAM_SUMMARY = 'summary';
    const DATE_FORMAT = 'Y-m-d H:i:s';
    const PARAM_SECURE_VALUE = 'ssl';
    const PARAM_DBPOOL = 'dbPool';
    const CR = "\n";

    private $modelConfig;
    private $userModel;
    private $message;
    private $smtpMailerConfig;
    private $smtpMailer;
    private $from;
    private $to;
    private $cc;
    private $subject;
    private $body;
    private $inputApproval;
    private $error;
    private $errorCode;
    private $errorMessage;
    private $baseUrl;

    /**
     * init
     *
     */
    protected function init()
    {
        $this->modelConfig = $this->getApp()->getConfig()->getSettings(self::PARAM_DBPOOL);
        $this->userModel = new modelUser($this->modelConfig);
        $this->setSmtpConfig();
        $this->setSmtpMailer();
        $this->baseUrl = $this->getApp()->getRequest()->getBaseUrl();
        $this->message = new Message;
        $this->error = false;
        $this->errorCode = 0;
        $this->errorMessage = '';
    }

    /**
     * sendapproval
     *
     */
    final public function sendapproval()
    {
        $this->prepareTarget(self::DEBUG);
        $smtpResult = [];
        if (!$this->error) {
            try {
                $this->message->setFrom($this->from);
                $this->message->addTo($this->to);
                if ($this->cc) {
                    $this->message->addTo($this->cc);
                }
                $this->message->setSubject($this->subject);
                $this->message->setHTMLBody($this->body);
                try {
                    $this->smtpMailer->send($this->message);
                } catch (\Exception $exc) {
                    $this->setError($exc->getCode(), $exc->getMessage());
                }
                $smtpResult = [
                    self::PARAM_FROM => $this->from,
                    self::PARAM_TO => $this->to,
                    self::PARAM_CC => $this->cc,
                    self::PARAM_SUBJECT => $this->subject,
                ];
            } catch (\Exception $exc) {
                $this->setError($exc->getCode(), $exc->getMessage());
            }
        }
        $message = new \stdClass();
        $message->error = $this->error;
        $message->errorCode = $this->errorCode;
        $message->errorMessage = $this->errorMessage;
        $message->input = $this->inputApproval->get();
        $message->mail = $smtpResult;
        return $this->getJsonResponse($message);
    }

    /**
     * __destruct
     *
     */
    public function __destruct()
    {
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }

    /**
     * setInputApproval
     *
     */
    private function setInputApproval()
    {
        $this->inputApproval = $this->getInputApproval();
    }

    /**
     * setPrepareTarget
     *
     */
    private function prepareTarget($mode)
    {
        if (self::DEBUG) {
            list(
                $this->from,
                $this->to,
                $this->cc,
                $this->subject,
                $this->body
                ) = [
                self::DEBUG_FROM,
                self::DEBUG_TO,
                self::DEBUG_CC,
                self::DEBUG_SUBJECT,
                self::DEBUG_BODY . self::CR . date(self::DATE_FORMAT)
                ];
        } else {
            $this->setInputApproval();
            $isValid = $this->isValidApproval();
            if ($isValid) {
                $asker = $this->userModel->getById($this->inputApproval->uid);
                if ($asker) {
                    $this->from = 'CRA Ask Approval <' . $this->smtpMailerConfig['username'] . '>';
                    if ($referent = $this->userModel->getById($asker->fid)) {
                        $this->to = $referent->email;
                        $this->cc = $asker->email;
                        $this->subject = $this->getApprovalSubject();
                        $this->body = $this->getApprovalBody($asker, $referent);
                    } else {
                        $this->setError(3, 'unknown referent');
                    }
                } else {
                    $this->setError(2, 'unknown asker');
                }
            } else {
                $this->setError(1, 'invalid parameters');
            }
        }
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
     * isValidApproval
     *
     * @param Input $input
     * @return boolean
     */
    private function isValidApproval()
    {
        return (
            $this->inputApproval->{self::PARAM_MONTH} && $this->inputApproval->{self::PARAM_YEAR} && $this->inputApproval->{self::PARAM_UID} && $this->inputApproval->{self::PARAM_DMONTH} && $this->inputApproval->{self::PARAM_BDAYS}
            );
    }

    /**
     * setSmtpConfig
     *
     */
    private function setSmtpConfig()
    {
        $config = $this->getApp()->getInstance()->getConfig();
        $this->smtpMailerConfig = $config->getSettings(self::PARAM_APP)[self::PARAM_MAILER];
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
     * getInputApproval
     *
     * @return inputFilter
     */
    private function getInputApproval()
    {
        return new inputFilter(
            $this->getParams(),
            [
            self::PARAM_MONTH => FILTER_SANITIZE_NUMBER_INT,
            self::PARAM_YEAR => FILTER_SANITIZE_NUMBER_INT,
            self::PARAM_UID => FILTER_SANITIZE_NUMBER_INT,
            self::PARAM_DMONTH => FILTER_SANITIZE_NUMBER_INT,
            self::PARAM_BDAYS => FILTER_SANITIZE_NUMBER_FLOAT
            ]
        );
    }

    /**
     * getApprovalSubject
     *
     * @return string
     */
    private function getApprovalSubject()
    {
        return 'Approval - CRA - ' . $this->inputApproval->{self::PARAM_YEAR} . '-'
            . $this->inputApproval->{self::PARAM_MONTH};
    }

    /**
     * getApprovalBody
     *
     * @param \App1\Model\Domain\User $asker
     * @return type
     */
    private function getApprovalBody($asker, $referent)
    {
        $approvalUrlLink = new mailDecorator(
            'a',
            ' ce lien d\'approbation ',
            [
            'href' => $this->getApprovalUrl($asker),
            'target' => '_blank'
            ]
        );
        $body = "<p>Je soussigné <i>" . $asker->{self::PARAM_NAME}
            . '</i> identifié par mon email '
            . '<i>' . $asker->{self::PARAM_EMAIL} . "</i>,</p>"
            . "<p> certifie sur l'honneur avoir travaillé "
            . $this->inputApproval->{self::PARAM_BDAYS} . ' jours '
            . ' sur un total de ' . $this->inputApproval->{self::PARAM_DMONTH}
            . ' jours ouvrés au cours du mois '
            . $this->inputApproval->{self::PARAM_MONTH}
            . ' de l\'année ' . $this->inputApproval->{self::PARAM_YEAR} . ".</p>"
            . '<p>A ce titre je demande à mon référent '
            . '<i>' . $referent->{self::PARAM_NAME} . '</i>'
            . "</i> identifié par l'email <i>" . $referent->{self::PARAM_EMAIL} . '</i>,</p>'
            . ' de bien vouloir valider ce décompte sur '
            . $approvalUrlLink . '.</p>'
            . '<p>Cordialement.</p>';
        return $body;
    }

    /**
     * getApprovalUrl
     *
     * @param type $asker
     * @return type
     */
    private function getApprovalUrl($asker)
    {
        return $this->baseUrl . '/approval/manage/' . self::PARAM_UID
            . DIRECTORY_SEPARATOR . $asker->{self::PARAM_ID}
            . DIRECTORY_SEPARATOR . self::PARAM_YEAR . DIRECTORY_SEPARATOR
            . $this->inputApproval->{self::PARAM_YEAR}
            . DIRECTORY_SEPARATOR . self::PARAM_MONTH . DIRECTORY_SEPARATOR
            . $this->inputApproval->{self::PARAM_MONTH};
    }
}
