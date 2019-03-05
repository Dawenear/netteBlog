<?php

namespace App\Model;

use Latte\Engine;
use Nette\Database\Context;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Mail\SendmailMailer;
use Nette\SmartObject;
use Nette;

class MailManager
{
    use SmartObject;

    /** @var Context */
    private $database;

    public function __construct(Context $database)
    {
        $this->database = $database;
    }

    public function sendMail($email, $subject, $body,  $params = [], $from = 'info@dawenear.eu')
    {
        $latte = new Engine;

        $mail = new Message();
        $mail->setFrom($from)
            ->addTo($email)
            ->setSubject($subject)
            ->setHtmlBody($latte->renderToString(__DIR__ . 'mailTemplate/' . $body, $params));
        
        $this->send($mail);
    }

    /** @return SendmailMailer */
    private function getMailer()
    {
        return new SendmailMailer;
    }

    /**
     * Sends email.
     * @return void
     * @throws SendException
     */
    function send(Message $mail)
    {
        $mailer = $this->getMailer();

        $mailer->send($mail);
    }
}

// TODO tu je sql na patch verze
// SELECT * FROM (SELECT pv.*, p.patch_name, p.description FROM web_patch_version AS pv LEFT JOIN web_patch AS p ON pv.patch_id = p.id ORDER BY pv.id DESC) AS t GROUP BY t.patch_name