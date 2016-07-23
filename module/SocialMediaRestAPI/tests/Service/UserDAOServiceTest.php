<?php

namespace SocialMediaRestAPITest\Service;

use Core\Test\TestCase;
use Zend\StdLib\ArrayUtils;
use SocialMediaRestAPI\DAO\UserDAOInterface;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Model\Entity\User;
use Core\Model\DAO\Exception\DAOException;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class UserDAOServiceTest extends TestCase 
{
    public function setUp()
    {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testHasBeenRegisteredForDi() {
        $userDAOService = $this->getUserDAOService();
        $this->assertNotNull($userDAOService);
        $this->assertEquals(get_class($userDAOService), UserDAOService::class);
        return $userDAOService;
    }

    private function getUserDAOService() {
        return $this->getServiceManager()->get('SocialMediaRestAPI\Service\UserDAOService');
    }

    private function newUser($username = "lucas.s.abreu@gmail.com", 
                             $name = "Lucas dos Santos Abreu",
                             $password = '123456') {
        $user = new User();
        $user->setData([
            'name' => $name,
            'username' => $username,
            'password' => $password,
        ]);
        return $user;
    }

    /**
     * @depends testHasBeenRegisteredForDi
     * @convers UserDAOService::save
     * @convers UserDAOService::findById
     */
    public function testSaveAndRetrieveAUser($uDAO) {
        $user = $this->newUser('lucas.s.abreu@gmail.com', 'Lucas dos Santos Abreu', '123456');
        $user = $uDAO->save($user);

        $this->assertEquals($user->id, 1);
        $this->assertEquals($user->username, 'lucas.s.abreu@gmail.com');
        $this->assertEquals($user->name, 'Lucas dos Santos Abreu');
        $this->assertEquals($user->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')

        $user2 = $uDAO->findById(1);

        $this->assertEquals($user2->id, 1);
        $this->assertEquals($user2->username, 'lucas.s.abreu@gmail.com');
        $this->assertEquals($user2->name, 'Lucas dos Santos Abreu');
        $this->assertEquals($user2->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')

        $user2->name = "Lucas Abreu";
        $user = $uDAO->findById(1);
        $this->assertEquals($user2->name, 'Lucas Abreu');
        return $uDAO;
    }

    /**
     * @depends testSaveAndRetrieveAUser
     * @convers UserDAOService::findByUsername
     */
    public function testCanFindByUsername ($userDAOService) {

        $user = $this->newUser('alguem@gmail.com', 'Alguem', '123456');
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername('lucas.s.abreu@gmail.com');

        $this->assertNotNull($user2);

        $this->assertEquals($user2->username, 'alguem@gmail.com');
        $this->assertEquals($user2->name, 'Alguem');
        $this->assertEquals($user2->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')
    }

    /**
     * @depends testCanFindByUsername
     * @convers UserDAOService::save
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessageRegExp /Aready exists a User with the username \".+\"/ 
     */
    public function testCanHasOnlyOneUserPerUsername ($userDAOService) {
        $user = $this->newUser();
        $user->username = "lucas.s.abreu@gmail.com";
        $userDAOService->save($user);
    }

    /**
     * @depends testCanFindByUsername
     * @convers UserDAOService::save
     * @convers UserDAOService::findByUsername
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage "Username can't be changed !" 
     */
    public function testCantChangeUsername ($userDAOService) {
        $user = $this->newUser();
        $user->username = 'um@localhost.com';
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername('um@localhost.com');
        $user2->username = 'dois@localhost.com';
        $userDAOService->save($user);
    }

    /**
     * @depends testCanFindByUsername
     * @convers UserDAOService::save
     * @convers UserDAOService::findByUsername
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage "To change the password must use changeUserPassword method !" 
     */
    public function testCantChangePasswordOnSave ($userDAOService) {
        $user = $this->newUser();
        $user->password = '123456';
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername($user->username);
        $user2->password = '654321';
        $userDAOService->save($user);
    }

    /**
     * @depends testCantChangePasswordOnSave
     * @convers UserDAOService::save
     * @convers UserDAOService::findById
     */
    public function testChangeUsersPassword ($userDAOService) {
        $user = $this->newUser();
        $user->password = '123456';
        $userDAOService->save($user);

        $user = $userDAOService->changeUserPassword($user->username, '123456', '654321');
        $user = $userDAOService->findByUsername($user->username);
        $this->assertEquals($user->password, 'c33367701511b4f6020ec61ded352059'); // md5('654321')
    }
}