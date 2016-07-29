<?php

namespace SocialMediaRestAPI\Authentification\Resolver;

use SocialMediaRestAPI\Service\UserDAOService;
use Zend\Authentication\Adapter\Http;

class UserResolver implements Http\ResolverInterface {

    /*
     * @var UserDAOService
     */
    private $userDAOService;

    public function __construct (UserDAOService $userDAOService) {
        $this->userDAOService = $userDAOService;
    }

    /**
     * Calls for the username name into the database, retrieving the cripted password
     *
     * @param  string $username Username
     * @param  string $realm    Authentication Realm
     * @return string|false User's shared secret, if the user is found in the
     *         realm, false otherwise.
     * @throws Exception\ExceptionInterface
     */
    public function resolve($username, $realm, $password = null)
    {
        if (empty($username)) {
            throw new Http\Exception\InvalidArgumentException('Username is required');
        } elseif (!ctype_print($username) || strpos($username, ':') !== false) {
            throw new Http\Exception\InvalidArgumentException(
                'Username must consist only of printable characters, excluding the colon'
            );
        }

        if (empty($password)) {
            throw new Http\Exception\InvalidArgumentException('Password is required');
        }

        try {
            $user = $this->userDAOService->findByUsername($username);

            if ($user === null)
                return false;

            $password = md5($password);

            if ($password !== $user->password)
                return false;

            return [
                'realm' => $realm,
                'username' => $user->username,
                'user' => $user,
            ];

        } catch(\Exception $e) {
            throw new Http\Exception\RuntimeException("Failed to resolve user", 0, $e);
        }

        return false;
    }



}