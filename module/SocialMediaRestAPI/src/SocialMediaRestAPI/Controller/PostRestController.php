<?php

namespace SocialMediaRestAPI\Controller;

use Core\Controller\AbstractRestfulController;
use Core\Controller\ProcessQueryStringTrait;
use Core\Controller\AuthenticationHelperTrait;
use Core\Model\Entity\Entity;
use DateTime;
use SocialMediaRestAPI\Controller\Exception\ForbiddenModifyRequestException;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPI\Service\PostDAOService;
use Zend\View\Model\JsonModel;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\Authentication\AuthenticationService;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostRestController extends AbstractRestfulController
{
    use ProcessQueryStringTrait;
    use AuthenticationHelperTrait;

    /**
     * @var UserDAOService
     */
    protected $dao;

    /**
     * @var AuthenticationService
     */
    protected $authService;

    protected function entityToJson(Entity $post) {
        return [
            'id' => $post->id,
            'userId' => $post->user->id,
            'datePublish' => $post->datePublish->format('Y-m-d H:i:s'),
            'text' => $post->text,
        ];
    }

    public function __construct(PostDAOService $dao, AuthenticationService $authService) {
        $this->dao = $dao;
        $this->authService = $authService;
    }

    public function getList() {

        $queryString = $this->getQuery('q');
        $params = [];
        if ($queryString !== null)
            $params = $this->processQueryString($queryString, ['datePublish', 'text'], ['datePublish']);

        $limit = intval($this->getQuery('limit', 50));

        if ($limit > 50)
            return $this->returnError(403, sprintf("Maximum limit is 50, parameter used was %d", $limit));

        $offset = intval($this->getQuery('offset', 0));

        /* @var $paginator AdapterInterface */
        $paginador = $this->dao->getAdapterPaginator($params, [
            'datePublish' => 'DESC'
        ]);

        $return = $this->convertPaginatorToJson($paginador, $limit, $offset);

        return new JsonModel($return);
    }

    public function get($id) {
        $post = $this->dao->findById($id);

        if ($post === null)
            return $this->returnError(404, sprintf("Post %d does not exist !", $id));
        
        return new JsonModel([
            'result' => $this->entityToJson($post),
        ]);
    }

    public function create ($data) {
        $user = $this->getIdentity($this->authService)['user'];

        $post = new Post();
        $post->user = $user;
        $post->text = isset($data['text']) ? $data['text'] : null; 
        $post->datePublish = new DateTime('now');

        $post = $this->dao->save($post);
        $this->setStatusCode(201);
        return new JsonModel([
            'result' => $this->entityToJson($post),
        ]);
    }

    public function update($id, $data) {
        $user = $this->getIdentity($this->authService)['user'];

        $post = $this->dao->findById($id);

        if ($post === null)
            return $this->returnError(404, sprintf("Post %d does not exist !", $id));

        if ($post->user->id !== $user->id)
            throw new ForbiddenModifyRequestException();

        $post->text = isset($data['text']) ? $data['text'] : null; 

        $post = $this->dao->save($post);

        return new JsonModel([
            'result' => $this->entityToJson($post),
        ]);
    }

    public function delete($id) {
        $user = $this->getIdentity($this->authService)['user'];

        $post = $this->dao->findById($id);

        if ($post === null)
            return $this->returnError(404, sprintf("Post %d does not exist !", $id));

        if ($post->user->id !== $user->id)
            throw new ForbiddenModifyRequestException();

        $this->dao->remove($post);

        $this->setStatusCode(204);
        return new JsonModel();
    }

    public function byUserAction() {
        if ($this->getRequest()->getMethod() !== 'GET') {
            $this->response->setStatusCode(405);
            return [
                'content' => 'Method Not Allowed'
            ];
        }

        $userId = $this->params('userId');        
        $user = $this->dao->findUserById($userId);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $userId));

        $queryString = $this->getQuery('q');
        $params = [];
        if ($queryString !== null)
            $params = $this->processQueryString($queryString, ['datePublish', 'text'], ['datePublish']);

        $limit = intval($this->getQuery('limit', 50));

        if ($limit > 50)
            return $this->returnError(403, sprintf("Maximum limit is 50, parameter used was %d", $limit));

        $offset = intval($this->getQuery('offset', 0));

        /* @var $paginator AdapterInterface */
        $paginador = $this->dao->getUserPostsAdapterPaginator($user, $params);
        $return = $this->convertPaginatorToJson($paginador, $limit, $offset);
        return new JsonModel($return);
    }

    public function feedAction() {
        if ($this->getRequest()->getMethod() !== 'GET') {
            $this->response->setStatusCode(405);
            return [
                'content' => 'Method Not Allowed'
            ];
        }

        $user = $this->getIdentity($this->authService)['user'];

        $queryString = $this->getQuery('q');
        $params = [];
        if ($queryString !== null)
            $params = $this->processQueryString($queryString, ['datePublish', 'text'], ['datePublish']);

        $limit = intval($this->getQuery('limit', 50));

        if ($limit > 50)
            return $this->returnError(403, sprintf("Maximum limit is 50, parameter used was %d", $limit));

        $offset = intval($this->getQuery('offset', 0));

        /* @var $paginator AdapterInterface */
        $paginador = $this->dao->getUserFeedAdapterPaginator($user, $params);
        $return = $this->convertPaginatorToJson($paginador, $limit, $offset);
        return new JsonModel($return);
    }

}

