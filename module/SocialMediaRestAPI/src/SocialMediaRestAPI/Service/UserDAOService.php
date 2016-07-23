<?php

namespace SocialMediaRestAPI\Service;

use Core\Model\Entity\Entity;
use Core\Service\AbstractDAOService;
use SocialMediaRestAPI\Model\DAO\UserDAOInterface;
use SocialMediaRestAPI\Model\Entity\User;
use Core\Model\DAO\Exception\DAOException;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class UserDAOService extends AbstractDAOService implements UserDAOInterface
{
    
    public function save($ent, array $values = null) {

        /* @var $user User */
        $user = null;

        if ($ent === null) {
            $user = new User();
            unset($values['id']);
        } else {
            if (!($ent instanceof User))
                $user = $this->findById($ent);
            else 
                $user = $ent;
        }

        if ($values == null)
            $values = $user->getArrayCopy();

        if ($user->id !== null)
            $user = $this->findById($user->id);

        if (isset($values['password']) && $values['password'] !== $user->password) {
            $values['password'] = md5($values['password']);
        }

        $user = parent::save($user);
        return $user;
    }

}