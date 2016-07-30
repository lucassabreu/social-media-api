<?php

namespace SocialMediaRestAPI\Controller;

use Core\Controller\AbstractRestfulController;
use Core\Controller\AuthenticationHelperTrait;
use Core\Controller\ProcessQueryStringTrait;
use Core\Model\Entity\Entity;
use SocialMediaRestAPI\Controller\Exception\ForbiddenModifyRequestException;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Traits\UserHelperTrait;
use Zend\Authentication\AuthenticationService;
use Zend\Paginator\Adapter\AdapterInterface;
use Zend\View\Model\JsonModel;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class FriendRestController extends AbstractRestfulController
{
    use ProcessQueryStringTrait;
    use UserHelperTrait;
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

    /**
     * Converts a list of users into Json
     * @param $users a iterable object with users
     * @return array users in json format 
     */
    protected function listToJson($users) {
        $result = [];
        foreach($users as $u)
            $result[] = $this->entityToJson($u);        
        return $result;
    }

    public function __construct(UserDAOService $dao, AuthenticationService $authService) {
        $this->dao = $dao;
        $this->authService = $authService;
    }

    public function getList() {
        $userId = $this->params('userId');
        $user = $this->dao->findById($userId);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $userId));

        return new JsonModel([
            'result' => $this->listToJson($user->getFriends()),
        ]);
    }

    public function create ($data) {

        $identityUser = $this->getIdentity($this->authService)['user'];
        $userId = $this->params('userId');
        $id = isset($data['id']) ? $data['id'] : null;

        if ($identityUser->id != $userId)
            throw new ForbiddenModifyRequestException();

        $user = $this->dao->findById($userId);

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $userId));

        if ($id === null || $id === "")
            $friend = null;
        else
            $friend = $this->dao->findById($id);

        $this->dao->createFriendship($user, $friend);

        $this->setStatusCode(201);
        return new JsonModel([
            'result' => $this->entityToJson($friend),
        ]);
    }

    public function delete($id) {

        $identityUser = $this->getIdentity($this->authService)['user'];
        $userId = $this->params('userId');
        $user = $this->dao->findById($userId);

        if ($identityUser->id != $userId)
            throw new ForbiddenModifyRequestException();

        if ($user === null)
            return $this->returnError(404, sprintf("User %d does not exist !", $id));

        if ($id === null || $id === "")
            $friend = null;
        else
            $friend = $this->dao->findById($id);

        $this->dao->removeFriendship($user, $friend);

        $this->setStatusCode(204);
        return new JsonModel();
    }

}

