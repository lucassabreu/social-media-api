<?php

namespace SocialMediaRestAPITest\Traits;

use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPI\Service\PostDAOService;
use SocialMediaRestAPITest\Traits\UserTestTrait;
use DateTime;

/**
 * Some methods to help test post, user and related things
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @see \SocialMediaRestAPI\Model\Entity\Post
 * @see \SocialMediaRestAPI\Model\Entity\User
 * @see \SocialMediaRestAPI\Service\PostDAOService
 * @see \SocialMediaRestAPI\Service\UserDAOService
 */
trait ModelHelpTestTrait
{

    /**
     * Retrieve the <code>UserDAOService</code>
     * @return UserDAOService
     */
    private function getUserDAOService()
    {
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
                             $password = '123456')
    {
        $user = new User();
        $user->setData([
            'name' => $name,
            'username' => $username,
            'password' => $password,
        ]);
        return $user;
    }

    /**
     * Create the number of users passed by parameter
     * @param $howMany Number of users to be created
     * @return array Array with the created users
     */
    private function createGenericUsers($howMany)
    {
        $userDAOService = $this->getUserDAOService();
        $users = [];
        for ($i = 1; $i <= $howMany; $i++) {
            $users[] = $userDAOService->save(
                $this->newUser("user$i@localhost.net",
                               "Usuário $i"));
        }
        return $users;
    }

    /**
     * Retrieve the <code>PostDAOService</code>
     * @return PostDAOService
     */
    private function getPostDAOService()
    {
        return $this->getServiceManager()->get('SocialMediaRestAPI\Service\PostDAOService');
    }

    /**
     * Create a new Post based on parameters
     * @param string $text
     * @param User $user
     * @param string|DateTime $datePublish
     * @return Post
     */
    private function newPost($text = "a generic post", User $user = null, $datePublish = null)
    {
        $datePublish = $datePublish === null ? new DateTime() : $datePublish;
        $user = $user !== null ? $user : $this->createGenericUsers(1)[0];
        
        $post = new Post();
        $post->user = $user;
        $post->datePublish = $datePublish;
        $post->text = $text;
        
        return $post;
    }

    /**
     * Create generic posts for a user
     * @param User $user
     * @param int $qtt number of posts to be created
     * @return array posts
     */
    private function createGenericPosts(User $user, $qtt)
    {
        $postDAOService = $this->getPostDAOService();
        $posts = [];
        for ($i = 1; $i <= $qtt; $i++) {
            $posts[] = $postDAOService->save(
                $this->newPost("the post number $i from $user->name", $user)
            );
        }
        return $posts;
    }
}
