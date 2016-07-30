<?php

namespace SocialMediaRestAPI\Controller;

use Core\Controller\AbstractRestfulController;
use Core\Controller\AuthenticationHelperTrait;
use Core\Controller\ProcessQueryStringTrait;
use Core\Model\Entity\Entity;
use SocialMediaRestAPI\Controller\Exception\ForbiddenModifyRequestException;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Service\UserDAOService;
use Zend\Authentication\AuthenticationService;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class UserRestController extends AbstractRestfulController
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

    protected function entityToJson(Entity $user) {
        return [
            'id' => $user->id,
            'name' => $user->name,
        ];
    }

    public function __construct(UserDAOService $dao, AuthenticationService $authService) {
        $this->dao = $dao;
        $this->authService = $authService;
    }

    public function getList() {

        $queryString = $this->getQuery('q');
        $params = [];
        if ($queryString !== null)
            $params = $this->processQueryString($queryString, ['name']);

        $limit = intval($this->getQuery('limit', 50));

        if ($limit > 50)
            return $this->returnError(403, sprintf("Maximum limit is 50, parameter used was %d", $limit));

        $offset = intval($this->getQuery('offset', 0));

        /* @var $paginator AdapterInterface */
        $paginador = $this->dao->getAdapterPaginator($params);

        $return = $this->convertPaginatorToJson($paginador, $limit, $offset);

        return new JsonModel($return);
    }

    public function get($id) {
        $user = $this->dao->findById($id);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));
        
        return new JsonModel([
            'result' => $this->entityToJson($user),
        ]);
    }

    public function create ($data) {
        $user = new User();

        $user->name = isset($data['name']) ? $data['name'] : null; 
        $user->username = isset($data['username']) ? $data['username'] : null; 
        $user->password = isset($data['password']) ? $data['password'] : null;
        
        $user = $this->dao->save($user);
        $this->setStatusCode(201);
        return new JsonModel([
            'result' => $this->entityToJson($user),
        ]);
    }

    public function update($id, $data) {

        $identityUser = $this->getIdentity($this->authService)['user'];

        if ($identityUser->id != $id)
            throw new ForbiddenModifyRequestException();

        $user = $this->dao->findById($id);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));

        $user->name = isset($data['name']) ? $data['name'] : null; 

        $user = $this->dao->save($user);

        return new JsonModel([
            'result' => $this->entityToJson($user),
        ]);
    }

    public function delete($id) {

        $identityUser = $this->getIdentity($this->authService)['user'];

        if ($identityUser->id != $id)
            throw new ForbiddenModifyRequestException();

        $user = $this->dao->findById($id);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));

        $this->dao->remove($user);

        $this->setStatusCode(204);
        return new JsonModel();
    }

    public function changePasswordAction() {

        if ($this->getRequest()->getMethod() !== 'PUT') {
            $this->response->setStatusCode(405);
            return [
                'content' => 'Method Not Allowed'
            ];
        }

        $identityUser = $this->getIdentity($this->authService)['user'];
        $id = $this->params('id');        
        $data = $this->processBodyContent($this->getRequest());

        if ($identityUser->id != $id)
            throw new ForbiddenModifyRequestException();

        $user = $this->dao->findById($id);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));

        $password = isset($data['password']) ? $data['password'] : null;
        $newPassword = isset($data['newPassword']) ? $data['newPassword'] : null;

        $this->dao->changeUserPassword($user, $password, $newPassword);

        return new JsonModel([
            'result' => [
                'success' => 'true'
            ]
        ]);
    }

    public function selfAction() {
        $identityUser = $this->getIdentity($this->authService)['user'];
        $user = $this->dao->findById($identityUser->id);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));
        
        return new JsonModel([
            'result' => $this->entityToJson($user),
        ]);
    }

}

