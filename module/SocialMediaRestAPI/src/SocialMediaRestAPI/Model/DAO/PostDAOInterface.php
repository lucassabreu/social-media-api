<?php

namespace SocialMediaRestAPI\Model\DAO;

use Core\Model\DAO\DAOInterface;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use Zend\Paginator\Adapter\AdapterInterface;

interface PostDAOInterface extends DAOInterface {
    
    /**
     * Retrieves an user's feed
     * @param mixed|User $user
     * @param array $params (optional) array with filters
     * @param int $limit (optional)
     * @param int $offset (optional)
     * @return array Posts on the feed
     */
    public function fetchUserFeed ($user, $params = [], $limit = null, $offset = null);

    /**
     * Returns a Paginator Adapter for the user's feed based on params.
     * @param array|mixed $params (optional) array with filters
     * @param array $orderBy (optional) posts order 
     * @return AdapterInterface
     */
    public function getUserFeedAdapterPaginator($user, $params = [], $orderBy = null);

    /**
     * Returns the posts created by parameters user
     * @param mixed|User $user
     * @param array $params (optional) array with filters
     * @param int $limit (optional)
     * @param int $offset (optional)
     * @return array user's posts
     */
    public function fetchUserPosts ($user, $params = [], $limit = null, $offset = null);

    /**
     * Returns a Paginator Adapter with the posts created by parameters user
     * @param mixed|User $user
     * @param array $params (optional) array with filters
     * @param array $orderBy (optional) posts order 
     * @return array user's posts
     */
    public function getUserPostsAdapterPaginator ($user, $params = [], $orderBy = null);

}