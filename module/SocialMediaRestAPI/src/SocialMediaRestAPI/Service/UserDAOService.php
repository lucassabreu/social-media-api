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

    public function remove (Entity $user) {

        if ($user === null || !($user instanceof User))
            throw new DAOException("Must be informmed a user to remove !");

        $friendList = [];
        foreach($user->getFriends() as $friend)
            $friendList[] = $friend;

        foreach ($friendList as $friend)
            $this->removeFriendship($user, $friend);
        
        return parent::remove($user);
    }
    
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

        if ($user->id !== null) {
            $oldUser = $this->findById($user->id);
            if ($oldUser !== null)
                $user->setData($oldUser->getData());
            else {
                $user->id = null;
                if (isset($values['id']))
                    unset($values['id']);
            }
        }

        if (isset($values['friends']))
            unset($values['friends']);

        if ($user->id === null) { // insert
            $values['password'] = md5($values['password']);
            $user->setData($values);
        } else { // update

            if (isset($values['username']) && $values['username'] != $user->username)
                throw new DAOException("Username can't be changed !");

            if (isset($values['password']) && $values['password'] !== $user->password) 
                throw new DAOException("To change the password must use changeUserPassword method !");
        }

        $otherUser = $this->findByUsername($user->username);

        if ($otherUser !== null && $otherUser->id !== $user->id)
            throw new DAOException(
                sprintf("Aready exists a User with the username \"%s\"",
                        $user->username));

        $user->setData($values);
        $user->validate();
        $user = parent::save($user);
        return $user;
    }

    /**
     * Retrieves the user with the parameter's username
     * @param $username
     * @return User
     */
    public function findByUsername ($username) {

        $users = $this->fetchByParams ([
            'username' => $username,
        ]);

        if (count($users) == 1)
            return $users[0];
        else
            return null;
    }

    /**
     * Change the parameter's user password for the $newPassword, only when $password 
     * is equal to tue current one
     * @param $user
     * @param $password
     * @param $newPassword
     * @return User
     */
    public function changeUserPassword ($ent, $password, $newPassword) {

        /* @var $user User */
        $user = null;

        if (!($ent instanceof User))
            $user = $this->findById($ent);
        else
            $user = $ent;

        if($user === null)
            throw new DAOException("This method can only be used for user that exists !");

        if($newPassword === null || trim($newPassword) == "")
            throw new DAOException("Must be informmed a new password !");

        $password = md5($password);

        if ($password !== $user->password)
            throw new DAOException("Password is not correct !");

        $user->password = md5($newPassword);
        $user = $this->dao->save($user);

        return $user;
    }

    /**
     * Create a Friendship between two users
     * @param $user
     * @param $friend
     * @return void
     */
    public function createFriendship($user, $friend) {

        if ($user !== null && !($user instanceof User))
            $user = $this->findById($user);

        if ($friend !== null && !($friend instanceof User))
            $friend = $this->findById($friend);

        if ($user === null || $friend === null)
            throw new DAOException("Must be informmed the two users to create a friendship !");

        if ($user->id === $friend->id)
            throw new DAOException("You can not befriend yourself !");

        $otherFriend = $this->returnFriendById($friend->id, $user->getFriends());

        if ($otherFriend !== null)
            throw new DAOException(sprintf("You and \"%s\" are aready friends !",
                                            $friend->name));

        $user->getFriends()->add($friend);
        $friend->getFriends()->add($user);

        $this->dao->save($user);
        $this->dao->save($friend);

        return;
    }

    /**
     * Remove a Friendship between two users
     * @param $user
     * @param $friend
     * @return void
     */
    public function removeFriendship ($user, $friend) {

        if ($user !== null && !($user instanceof User))
            $user = $this->findById($user);

        if ($friend !== null && !($friend instanceof User))
            $friend = $this->findById($friend);

        if ($user === null || $friend === null)
            throw new DAOException("Must be informmed the two users to remove a friendship !");

        if ($user->id === $friend->id)
            throw new DAOException("You can not unfriend yourself !");

        $otherFriend = $this->returnFriendById($friend->id, $user->getFriends());
        if ($otherFriend !== null)
            $user->getFriends()->removeElement($otherFriend);

        $otherFriend = $this->returnFriendById($user->id, $friend->getFriends());
        if ($otherFriend !== null)
            $friend->getFriends()->removeElement($otherFriend);

        $this->dao->save($user);
        $this->dao->save($friend);
    }

    /**
     * Find a user into the array of friends
     * @param $id User's Id
     * @param $friends User's friends
     * @return User
     */
    private function returnFriendById($id, $friends) {
        foreach($friends as $friend) {
            if($friend->id === $id)
                return $friend;
        }
    }
}