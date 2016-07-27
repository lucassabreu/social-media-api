<?php

namespace SocialMediaRestAPITest\Traits;

include_once __DIR__ . '/../Traits/UserTestTrait.php';

use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPI\Service\PostDAOService;
use SocialMediaRestAPITest\Traits\UserTestTrait;
use DateTime;

/**
 * Some methods to help test post related things
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @see \SocialMediaRestAPI\Model\Entity\Post
 * @see \SocialMediaRestAPI\Service\PostDAOService
 */
trait PostTestTrait {

    use UserTestTrait;

    /**
     * Retrieve the <code>PostDAOService</code>
     * @return PostDAOService
     */
    private function getPostDAOService() {
        return $this->getServiceManager()->get('SocialMediaRestAPI\Service\PostDAOService');
    }

    /**
     * Create a new Post based on parameters
     * @param string $text
     * @param User $user
     * @param string|DateTime $datePublish
     * @return Post
     */
    private function newPost($text = "a generic post", User $user = null, $datePublish = null) {
        
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
    private function createGenericPosts(User $user, $qtt) {
        $postDAOService = $this->getPostDAOService();
        $posts = [];
        for($i = 1; $i <= $qtt; $i++) {
            $posts[] = $postDAOService->save(
                $this->newPost("the post number $i from $user->name", $user)
            );
        }
        return $posts;
    }
}