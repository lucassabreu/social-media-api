<?php

namespace SocialMediaRestAPI\Model\Doctrine;

use SocialMediaRestAPI\Model\DAO\PostDAOInterface;
use Core\Model\DAO\Doctrine\AbstractDoctrineDAO;
use SocialMediaRestAPi\Model\Entity\Post;
use SocialMediaRestAPi\Model\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostDAODoctrine extends AbstractDoctrineDAO implements PostDAOInterface
{
    public function __construct () {
        parent::__construct('SocialMediaRestAPI\Model\Entity\Post');
    }

    /**
     * Build a query for the users feed based on parameters
     * @param User $user
     * @param array $params
     * @return QueryBuilder
     */
    private function buildUserFeedQuery($user, array $params, $limit = null, $offset = null) {

        $users = [];

        foreach($user->getFriends() as $friend) {
            $users[] = $friend;
        }

        $users[] = $user;
        $params['user'] = [
            'in' => $users
        ];

        $qb = $this->getQuery($params, $limit, $offset);

        return $qb;
    }

    /**
     * @override
     */
    public function fetchUserFeed ($user, array $params = [], $limit = null, $offset = null) {
        $qb = $this->buildUserFeedQuery($user, $params, $limit, $offset);
        $qb->orderBy("ent.datePublish", "DESC");
        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * @override
     */
    public function getUserFeedAdapterPaginator($user, array $params = [], $orderBy = null) {
        $qb = $this->buildUserFeedQuery($user, $params);

        if ($orderBy != null)
            foreach ($orderBy as $column => $order)
                $qb->orderBy("ent.$column", $order);
        else
            $qb->orderBy("ent.datePublish", "DESC");
        
        $paginator = new Paginator($qb->getQuery(), false); // problems with orderby that no one wants to solve
        $adapter = new DoctrinePaginator($paginator);

        return $adapter;
    }

    /**
     * Build a query for the users posts based on parameters
     * @param User $user
     * @param array $params
     * @return QueryBuilder
     */
    private function buildUsersPostQuery($user, array $params, $limit = null, $offset = null) {
        $params['user'] = $user;
        $qb = $this->getQuery($params, $limit, $offset);
        return $qb;
    }

    /**
     * @override
     */
    public function fetchUserPosts ($user, array $params = [], $limit = null, $offset = null) {
        $qb = $this->buildUsersPostQuery($user, $params, $limit, $offset);
        $qb->orderBy("ent.datePublish", "DESC");
        $query = $qb->getQuery();
        return $query->execute();
    }

    /**
     * @override
     */
    public function getUserPostsAdapterPaginator ($user, array $params = [], $orderBy = null) {
        $qb = $this->buildUsersPostQuery($user, $params);

        if ($orderBy != null)
            foreach ($orderBy as $column => $order)
                $qb->orderBy("ent.$column", $order);
        else
            $qb->orderBy("ent.datePublish", "DESC");
        
        $paginator = new Paginator($qb->getQuery(), false); // problems with orderby that no one wants to solve
        $adapter = new DoctrinePaginator($paginator);

        return $adapter;
    }

}
