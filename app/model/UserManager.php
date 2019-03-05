<?php

namespace App\Model;

use App\Exceptions\InvalidTokenException;
use Nette\Security as NS;
use Nette\Utils\Strings;
use Nette;

class UserManager implements NS\IAuthenticator
{
    public $database;

    const REGISTERED = 'registered';
    const GAMEMASTER = 'gamemaster';
    const ADMIN = 'admin';
    const GUEST = 'guest';
    const TABLE_ACCOUNTS = 'account';
    const TABLE_TOKENS = 'web_tokens';
    const COLUMN_NAME = 'username';
    const COLUMN_EMAIL = 'email';
    const COLUMN_PASSWORD = 'sha_pass_hash';

    function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    /**
     * @param array $credentials
     *
     * @return NS\Identity|NS\IIdentity
     *
     * @throws NS\AuthenticationException
     */
    function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->database->table('account')
            ->where('username', Strings::upper($username))
            ->select('account.*')
            ->fetch();

        if (!$row) {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!$this->verifyPassword($username, $password, $row)) {
            throw new NS\AuthenticationException('Invalid password.');
        }

        return new NS\Identity($row->id, $row->role, ['username' => $row->username]);
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public function checkToken($token)
    {
        if (!$token) {
            return false;
        }

        $checkToken = $this->database->table(self::TABLE_TOKENS)
            ->where('token = ? AND expiration > ?', $token, new \DateTime)
            ->fetch();

        if (!$checkToken) {
            return false;
        }

        return true;
    }

    /**
     * @param $password
     * @param $token
     *
     * @throws DuplicateNameException
     * @throws InvalidTokenException
     */
    public function addUser($password, $token)
    {
        try {
            $userData = $this->database->table(self::TABLE_TOKENS)
                ->where('token', $token)
                ->where('expiration > ?', new \DateTime)
                ->fetch();
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new InvalidTokenException;
        }
        try {
            $this->database->table(self::TABLE_ACCOUNTS)->insert([
                self::COLUMN_NAME => $userData->username,
                self::COLUMN_PASSWORD => Strings::upper(sha1(Strings::upper($userData->username) . ':' . Strings::upper($password))),
                self::COLUMN_EMAIL => $userData->email,
            ]);
        } catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
        }

        $this->database->table(self::TABLE_TOKENS)->where('token', $token)->delete();
    }

    private function verifyPassword($username, $password, $row)
    {
        $pass = Strings::upper(sha1(Strings::upper($username) . ':' . Strings::upper($password)));
        if ($row->sha_pass_hash === $pass) {
            return true;
        }
        return false;
    }
}

