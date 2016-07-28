<?php

namespace SocialMediaRestAPI\Service;

use DateTime;
use Core\Model\Entity\Entity;
use Core\Service\AbstractDAOService;
use Core\Model\DAO\Exception\DAOException;
use SocialMediaRestAPI\Model\DAO\PostDAOInterface;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Traits\UserHelperTrait;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostDAOService extends AbstractDAOService implements PostDAOInterface
{

    /**
     * @var UserDAOService
     */
    private $userDAOService;

    public function __construct (UserDAOService $userDAOService) {
        $this->userDAOService = $userDAOService; 
    }

    public function save($ent, array $values = null) {

        /* @var $post Post */
        $post = null;

        if ($ent === null) {
            $post = new Post();
            unset($values['id']);
        } else {
            if (!($ent instanceof Post))
                $post = $this->findById($ent);
            else 
                $post = $ent;
        }

        if ($values === null)
            $values = $post->getData();

        if (isset($values['user'])) {
            if ($values['user'] === null) {
                unset($values['user']);
            } else {
                if (!($values['user'] instanceof User) || $values['user']->id === null) 
                    throw new DAOException("Must be informmed a valid User !");

                $values['user'] = $this->userDAOService->findById($values['user']->id);
                if ($values['user'] === null)
                    throw new DAOException("Must be informmed a valid User !");
            }
        }

        if (isset($values['datePublish'])) {
            if ($values['datePublish'] === null) {
                unset($values['datePublish']);
            } else {
                if (!($values['datePublish'] instanceof DateTime))
                    $values['datePublish'] = DateTime::createFromFormat("!Y-m-d H:i:s", $values['datePublish']);
            }
        }

        if ($post->id !== null) {

            // to refresh against database
            $post = $this->findById($post->id);

            if (isset($values['datePublish']) &&
                ($values['datePublish'] === null || 
                 $values['datePublish']->getTimestamp() !== $post->datePublish->getTimestamp()))
                throw new DAOException("Publish date cannot be changed !");

            if (isset($values['user']) && $values['user']->id !== $post->user->id)
                throw new DAOException("User cannot be changed !");
        } else {

            if (!isset($values['datePublish']) || $values['datePublish'] == null)
                throw new DAOException("Must be informmed a valid publish date !");

            if (!isset($values['user']) || $values['user'] == null)
                throw new DAOException("Must be informmed a valid User !");

        }

        $post->setData($values);
        $post->validate();
        $post = $this->dao->save($post);
        return $post;
    }

    /**
     * @override
     */
    public function fetchUserFeed ($user, array $params = [], $limit = null, $offset = null) {

        if ($user === null)
            throw new DAOException("Must inform a user to list posts !");

        if (!($user instanceof User))
            $user = $this->userDAOService->findById($user);

        return $this->dao->fetchUserFeed($user, $params, $limit, $offset);
    }

    /**
     * @override
     */
    public function getUserFeedAdapterPaginator($user, array $params = [], $orderBy = null) {

        if ($user === null)
            throw new DAOException("Must inform a user to list posts !");

        if (!($user instanceof User))
            $user = $this->userDAOService->findById($user);

        return $this->dao->getUserFeedAdapterPaginator($user, $params, $orderBy);
    }

    /**
     * @override
     */
    public function fetchUserPosts ($user, array $params = [], $limit = null, $offset = null) {

        if ($user === null)
            throw new DAOException("Must inform a user to list posts !");

        if (!($user instanceof User))
            $user = $this->userDAOService->findById($user);

        return $this->dao->fetchUserPosts($user, $params, $limit, $offset);
    }

    /**
     * @override
     */
    public function getUserPostsAdapterPaginator ($user, array $params = [], $orderBy = null) {

        if ($user === null)
            throw new DAOException("Must inform a user to list posts !");

        if (!($user instanceof User))
            $user = $this->userDAOService->findById($user);

        return $this->dao->getUserPostsAdapterPaginator($user, $params, $orderBy);
    }
}