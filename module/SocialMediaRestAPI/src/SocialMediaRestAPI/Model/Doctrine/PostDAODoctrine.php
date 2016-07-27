<?php

namespace SocialMediaRestAPI\Model\Doctrine;

use SocialMediaRestAPI\Model\DAO\PostDAOInterface;
use Core\Model\DAO\Doctrine\AbstractDoctrineDAO;

class PostDAODoctrine extends AbstractDoctrineDAO implements PostDAOInterface
{
    public function __construct () {
        parent::__construct('SocialMediaRestAPI\Model\Entity\Post');
    }

    /**
     * @override
     */
    public function fetchUserFeed ($user, $params = [], $limit = null, $offset = null) {
        throw new \Exception('Not implemented');
    }

    /**
     * @override
     */
    public function getUserFeedAdapterPaginator($user, $params = [], $orderBy = null) {
        throw new \Exception('Not implemented');
    }

    /**
     * @override
     */
    public function fetchUserPosts ($user, $params = [], $limit = null, $offset = null) {
        throw new \Exception('Not implemented');
    }

    /**
     * @override
     */
    public function getUserPostsAdapterPaginator ($user, $params = [], $orderBy = null) {
        throw new \Exception('Not implemented');
    }

}
