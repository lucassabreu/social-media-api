<?php

namespace SocialMediaRestAPI\Traits;

/**
 * Has helper methods related with users
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
trait UserHelperTrait
{

    /**
     * Find a user into the array of friends
     * @param $id User's Id
     * @param $friends User's friends
     * @return User
     */
    private function returnFriendById($id, $friends)
    {
        foreach ($friends as $friend) {
            if ($friend->id === $id) {
                return $friend;
            }
        }
    }
}
