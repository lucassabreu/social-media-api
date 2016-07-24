<?php

namespace SocialMediaRestAPITest\Traits;

use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Service\UserDAOService;

/**
 * Some methods to help test user related things
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @see \SocialMediaRestAPI\Model\Entity\User
 * @see \SocialMediaRestAPI\Service\UserDAOService
 */
trait UserTestTrait {

    /**
     * Retrieve the <code>UserDAOService</code>
     * @return UserDAOService
     */
    private function getUserDAOService() {
        return $this->getServiceManager()->get('SocialMediaRestAPI\Service\UserDAOService');
    }

    /**
     * Create a new user instance based on parameters
     * @param $username optional default = lucas.s.abreu@gmail.com
     * @param $name optional default = Lucas dos Santos Abreu
     * @param $password optional default = 123465
     * @return User
     */
    private function newUser($username = "lucas.s.abreu@gmail.com", 
                             $name = "Lucas dos Santos Abreu",
                             $password = '123456') {
        $user = new User();
        $user->setData([
            'name' => $name,
            'username' => $username,
            'password' => $password,
        ]);
        return $user;
    }

}