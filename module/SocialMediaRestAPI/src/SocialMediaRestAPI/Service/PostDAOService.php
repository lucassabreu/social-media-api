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
    public function getUserPostsAdapterPaginator ($user, $params = [], $limit = null, $offset = null) {
        throw new \Exception('Not implemented');
    }
}