<?php

namespace App\Model;

use App\Exceptions\DuplicateEmailException;
use App\Exceptions\HandledRequestException;
use Nette;
use Nette\Utils\Random;
use Nette\Utils\Strings;

class RequestManager
{
    use Nette\SmartObject;

    /** @var Nette\Database\Context */
    private $db;

    const TABLE_REQUESTS = 'web_requests';
    const TABLE_TOKENS = 'web_tokens';
    const TABLE_ACCOUNTS = 'account';

    const MAIL_ACCEPT = 'register.latte';
    const MAIL_REFUSE = 'deny.latte';

    const COLUMN_USERNAME = 'username';
    const COLUMN_TYPE = 'type';
    const COLUMN_TOKEN = 'token';
    const COLUMN_EMAIL = 'email';
    const COLUMN_ACTIVE = 'active';
    const COLUMN_EXPIRATION = 'expiration';

    /** @var MailManager */
    private $mailManager;

    /**
     * RequestManager constructor.
     *
     * @param Nette\Database\Context $database
     * @param MailManager $mailManager
     */
    public function __construct(Nette\Database\Context $database, MailManager $mailManager)
    {
        $this->db = $database;
        $this->mailManager = $mailManager;
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function getRequests()
    {
        return $this->db->table(self::TABLE_REQUESTS)
            ->where(self::COLUMN_ACTIVE, 1);
    }

    /**
     * @param int $id
     * @param string $type
     *
     * @throws HandledRequestException
     * @throws \Exception
     */
    public function handleRequest($id, $type)
    {
        $request = $this->db->table(self::TABLE_REQUESTS)->where('id', $id)->fetch();

        if (!$request->active) {
            throw new HandledRequestException;
        }

        switch ($type) {
            case 'accept':
                $token = $this->createToken($request->username, $request->email);
                $this->sendEmail($request->email, 'Schválení žádosti', self::MAIL_ACCEPT, ['token' => $token]);
                $this->db->table(self::TABLE_REQUESTS)
                    ->where('id', $id)
                    ->update([self::COLUMN_ACTIVE => 0]);
                break;
            case 'refuse':
                $this->sendEmail($request->email, 'Odmítnutí žádosti', self::MAIL_REFUSE);
                $this->db->table(self::TABLE_REQUESTS)
                    ->where('id', $id)
                    ->update([self::COLUMN_ACTIVE => 0]);
                break;
            case 'delete':
                $this->db->table(self::TABLE_REQUESTS)
                    ->where('id', $id)
                    ->delete();
                break;
        }

    }

    /**
     * @param $username
     * @param $email
     *
     * @return string
     * @throws \Exception
     */
    private function createToken($username, $email)
    {
        $token = Random::generate(10);
        $date = new \DateTime;
        $date->add(new \DateInterval('P14D'));

        $values = [
            self::COLUMN_USERNAME => $username,
            self::COLUMN_TYPE => 0,
            self::COLUMN_TOKEN => $token,
            self::COLUMN_EMAIL => $email,
            self::COLUMN_EXPIRATION => $date,
        ];
        $this->db->table(self::TABLE_TOKENS)->insert($values);

        return $token;
    }

    private function sendEmail($email, $subject, $body, $options = [])
    {
        $this->mailManager->sendMail($email, $subject, $body, $options);
    }

    /**
     * @param $values
     *
     * @throws DuplicateNameException
     * @throws DuplicateEmailException
     */
    public function createRequest($values)
    {
        $account = $this->db->table(self::TABLE_ACCOUNTS)->where('username = ? OR email = ?', $values->username, $values->email)->fetch();
        if ($account) {
            if (Strings::upper($account->username) === Strings::upper($values->username)) {
                throw new DuplicateNameException;
            }
            if (Strings::upper($account->email) === Strings::upper($values->email)) {
                throw new DuplicateEmailException;
            }
        }

        $requests = $this->db->table(self::TABLE_REQUESTS)->where('username = ? OR email = ?', $values->username, $values->email)->fetch();
        if ($requests) {
            if (Strings::upper($requests->username) === Strings::upper($values->username)) {
                throw new DuplicateNameException;
            }
            if (Strings::upper($requests->email) === Strings::upper($values->email)) {
                throw new DuplicateEmailException;
            }
        }

        $tokens = $this->db->table(self::TABLE_TOKENS)->where('(username = ? OR email = ?) AND type = ? AND expiration > ?', $values->username, $values->email, 0, new \DateTime)->fetch();
        if ($tokens) {
            if (Strings::upper($tokens->username) === Strings::upper($tokens->username)) {
                throw new DuplicateNameException;
            }
            if (Strings::upper($tokens->email) === Strings::upper($tokens->email)) {
                throw new DuplicateEmailException;
            }
        }

        $this->db->table(self::TABLE_REQUESTS)->insert($values);
    }
}