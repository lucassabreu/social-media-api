<?php

namespace SocialMediaRestAPI\Service;

use Core\Model\Entity\Entity;
use Core\Service\AbstractDAOService;
use SocialMediaRestAPI\Model\DAO\PostDAOInterface;
use SocialMediaRestAPI\Model\Entity\User;
use Core\Model\DAO\Exception\DAOException;
use SocialMediaRestAPI\Traits\UserHelperTrait;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostDAOService extends AbstractDAOService implements PostDAOInterface
{
    /**
     * @override
     */
    public function fetchUserFeed ($user, $params = [], $limit = null, $offset = null) {
        return $this->dao->fetchUserFeed($user, $params, $limit, $offset);
    }

    /**
     * @override
     */
    public function getUserFeedAdapterPaginator($user, $params = [], $orderBy = null) {
        return $this->dao->getUserFeedAdapterPaginator($user, $params, $orderBy);
    }

    /**
     * @override
     */
    public function fetchUserPosts ($user, $params = [], $limit = null, $offset = null) {
        return $this->dao->fetchUserPosts($user, $params, $limit, $offset);
    }

    /**
     * @override
     */
    public function getUserPostsAdapterPaginator ($user, $params = [], $orderBy = null) {
        return $this->dao->fetchUserPosts($user, $params, $orderBy);
    }
}