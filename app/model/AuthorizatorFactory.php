<?php

namespace App\Model;

use Nette;

class AuthorizatorFactory
{
    /**
     * @return Nette\Security\Permission
     */
    public static function create()
    {
        $acl = new Nette\Security\Permission;

        // pokud chceme, můžeme role a zdroje načíst z databáze
        $acl->addRole('guest');
        $acl->addRole('storyteller', 'user');
        $acl->addRole('admin', 'storyteller');

        return $acl;
    }
}