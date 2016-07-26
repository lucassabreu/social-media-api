<?php

namespace SocialMediaRestAPI\Controller;

use Core\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use SocialMediaRestAPI\Service\UserDAOService;
use Core\Controller\ProcessQueryStringTrait;
use Zend\Paginator\Adapter\AdapterInterface;
use Core\Model\Entity\Entity;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Traits\UserHelperTrait;

class FriendRestController extends AbstractRestfulController
{
    use ProcessQueryStringTrait;
    use UserHelperTrait;

    protected $dao;

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

    public function __construct(UserDAOService $dao) {
        $this->dao = $dao;
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
        $userId = $this->params('userId');
        $id = isset($data['id']) ? $data['id'] : null;

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
        $userId = $this->params('userId');
        $user = $this->dao->findById($userId);

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

