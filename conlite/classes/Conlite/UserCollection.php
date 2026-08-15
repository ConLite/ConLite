<?php

namespace ConLite\Conlite;

use ConLite\Exceptions\Exception;
use ConLite\GenericDb\Item;
use ConLite\GenericDb\ItemCollection;

class UserCollection extends ItemCollection
{
    public function __construct($select = false) {
        parent::__construct(\cRegistry::getConfigValue('tab','phplib_auth_user_md5'), "user_id");
        $this->_setItemClass("ConLite\\Conlite\\User");
        if ($select !== false) {
            $this->select($select);
        }
    }

    /**
     * @throws Exception
     */
    public function create(string $username): bool|User
    {
        $md5user = md5($username);

        $this->resetQuery();
        $this->setWhere("user_id", $md5user);
        $this->query();

        if ($this->next()) {
            return false;
        } else {
            /**
             * @var User $item
             */
            $item = parent::createNewItem();
            $item->set("user_id", $md5user);
            $item->set("username", $username);
            $item->store();

            return $item;
        }
    }

    public function deleteUserByUsername($username): bool
    {
        return $this->deleteBy('username', $username) > 0;
    }

}