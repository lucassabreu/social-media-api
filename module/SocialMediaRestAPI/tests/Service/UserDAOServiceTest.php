<?php

namespace SocialMediaRestAPITest\Service;

include_once __DIR__ . '/../Traits/ModelHelpTestTrait.php';

use Core\Test\TestCase;
use Zend\StdLib\ArrayUtils;
use SocialMediaRestAPI\DAO\UserDAOInterface;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Model\Entity\User;
use Core\Model\DAO\Exception\DAOException;
use SocialMediaRestAPITest\Traits\ModelHelpTestTrait;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class UserDAOServiceTest extends TestCase
{
    use ModelHelpTestTrait;

    public function setUp() {
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

    /**
     * @depends testHasBeenRegisteredForDi
     * @covers UserDAOService::save
     * @covers UserDAOService::findById
     */
    public function testSaveAndRetrieveAUser() {
        $uDAO = $this->getUserDAOService();
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
        $uDAO->save($user2);

        $user = $uDAO->findById(1);
        $this->assertEquals($user->name, 'Lucas Abreu');
        return $uDAO;
    }

    /**
     * @depends testSaveAndRetrieveAUser
     * @covers UserDAOService::findByUsername
     */
    public function testCanFindByUsername () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser('alguem@gmail.com', 'Alguem', '123456');
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername('alguem@gmail.com');

        $this->assertNotNull($user2);

        $this->assertEquals($user2->username, 'alguem@gmail.com');
        $this->assertEquals($user2->name, 'Alguem');
        $this->assertEquals($user2->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')
        return $userDAOService;
    }

    /**
     * @covers UserDAOService::save
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessageRegExp /Aready exists a User with the username \".+\"/ 
     */
    public function testCanHasOnlyOneUserByUsername () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser();
        $user->username = "lucas.s.abreu@gmail.com";
        $userDAOService->save($user);

        /// creating a second one
        $user = $this->newUser();
        $user->username = "lucas.s.abreu@gmail.com";
        $userDAOService->save($user);
    }

    /**
     * @covers UserDAOService::save
     * @covers UserDAOService::findByUsername
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Username can't be changed ! 
     */
    public function testCantChangeUsername () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser();
        $user->username = 'um@localhost.com';
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername('um@localhost.com');
        $user2->username = 'dois@localhost.com';

        $userDAOService->save($user2);
    }

    /**
     * @depends testCanFindByUsername
     * @covers UserDAOService::save
     * @covers UserDAOService::findByUsername
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage To change the password must use changeUserPassword method ! 
     */
    public function testCantChangePasswordOnSave () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser('lucas.s.abreu2@gmail.com');
        $user->password = '123456';
        $userDAOService->save($user);

        $user2 = $userDAOService->findByUsername($user->username);
        $user2->password = '654321';
        $userDAOService->save($user);

        return $userDAOService;
    }

    /**
     * @depends testCantChangePasswordOnSave
     * @covers UserDAOService::save
     * @covers UserDAOService::changeUserPassword
     * @covers UserDAOService::findByUsername
     */
    public function testChangeUsersPassword () {

        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser("lucas@localhost.com");
        $user->password = '123456';
        $userDAOService->save($user);

        $user = $userDAOService->changeUserPassword($user, '123456', '654321');
        $user = $userDAOService->findByUsername($user->username);
        $this->assertEquals($user->password, 'c33367701511b4f6020ec61ded352059'); // md5('654321')
    }

    /**
     * @depends testChangeUsersPassword
     * @covers UserDAOService::save
     * @covers UserDAOService::changeUserPassword
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Password is not correct !
     */
    public function testMustInformTheCurrentPasswordToChangeIt () {

        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser("lucas@localhost.com");
        $user->password = '123456';
        $userDAOService->save($user);

        $user = $userDAOService->changeUserPassword($user, 'errado', '654321');
    }

    /**
     * @depends testChangeUsersPassword
     * @covers UserDAOService::save
     * @covers UserDAOService::changeUserPassword
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must be informmed a new password !
     */
    public function testMustInformTheNewPassword () {

        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser("lucas@localhost.com");
        $user->password = '123456';
        $userDAOService->save($user);

        $user = $userDAOService->changeUserPassword($user, '123456', '');
    }

    /**
     * @depends testCantChangePasswordOnSave
     * @covers UserDAOService::save
     * @covers UserDAOService::findById
     */
    public function testCantChangePasswordIfTheOldIsWrong () {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->findByUsername('lucas.s.abreu@gmail.com');        
    }

    public function testCanDeleteUsers() {

        $user1 = $this->newUser('user1@localhost.com');
        $user2 = $this->newUser('user2@localhost.com');

        $userDAOService = $this->getUserDAOService();

        $userDAOService->save($user1);
        $userDAOService->save($user2);

        $user = $userDAOService->findById(1);
        $this->assertEquals($user->username, $user1->username);

        $userDAOService->remove($user);
        $user = $userDAOService->findById(1);
        $this->assertNull($user);        
    }

    /**
     * @covers UserDAOService::createFriendship
     */
    public function testMakeFriends() {

        $userDAOService = $this->getUserDAOService();

        $user1 = $this->newUser('user0@localhost.net');
        $user2 = $this->newUser('user1@localhost.net');
        $user3 = $this->newUser('user2@localhost.net');

        $userDAOService->save($user1);
        $userDAOService->save($user2);
        $userDAOService->save($user3);

        $userDAOService->createFriendship($user1, $user2);

        $user = $userDAOService->findById(1);
        $friend = $user->getFriends()[0];

        $this->assertEquals($friend->id, $user2->id);

        $friend2 = $friend->getFriends()[0];
        $this->assertEquals($friend2->id, $user1->id);

        $this->assertEquals(count($user->getFriends()), 1);
        $this->assertEquals(count($friend->getFriends()), 1);

        $userDAOService->createFriendship($user1, $user3);
        
        $user = $userDAOService->findById(3);
        $friend = $user->getFriends()[0];

        $this->assertEquals($friend->id, $user1->id);

        $friend2 = $friend->getFriends()[1];
        $this->assertEquals($friend2->id, $user3->id);

        $this->assertEquals(count($user->getFriends()), 1);
        $this->assertEquals(count($friend->getFriends()), 2);

    }

    /**
     * @covers UserDAOService::removeFriendship
     * @depends testMakeFriends
     */
    public function testRemoveFriendship() {
        $users = [];

        $users[] = $this->newUser("user0@localhost.com");
        $users[] = $this->newUser("user1@localhost.com");

        $userDAOService = $this->getUserDAOService();

        $userDAOService->save($users[0]);
        $userDAOService->save($users[1]);
        $userDAOService->createFriendship($users[0], $users[1]);

        $this->assertEquals(count($users[0]->getFriends()), 1);
        $this->assertEquals(count($users[1]->getFriends()), 1);

        $userDAOService->removeFriendship($users[0], $users[1]);

        $this->assertEquals(count($users[0]->getFriends()), 0);
        $this->assertEquals(count($users[1]->getFriends()), 0);
    }

    /**
     * @covers UserDAOService::createFriendship
     * @depends testMakeFriends
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage You can not befriend yourself !
     */
    public function testCanBefriendYourself() {
        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser();

        $userDAOService->save($user);
        $userDAOService->createFriendship($user, $user);
    }

    /**
     * @covers UserDAOService::removeFriendship
     * @depends testRemoveFriendship
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessageRegExp /You and \".+\" are aready friends \!/
     */
    public function testOnlyOneFriendshipByDuo () {

        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser('user@localhost.net');
        $friend = $this->newUser('friend@localhost.net');

        $userDAOService->save($user);
        $userDAOService->save($friend);

        $userDAOService->createFriendship($user, $friend);

        $this->assertEquals(count($user->getFriends()), 1);
        $this->assertEquals(count($friend->getFriends()), 1);

        $userDAOService->createFriendship($user, $friend);
    }

    /**
     * @covers UserDAOService::createFriendship
     * @depends testMakeFriends
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must be informmed the two users to create a friendship !
     */
    public function testMustBeTwoToBeAFrienship () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser('user@localhost.net');

        $userDAOService->save($user);
        $userDAOService->createFriendship($user, null);
    }

    /**
     * @covers UserDAOService::createFriendship
     * @depends testMakeFriends
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must be informmed the two users to remove a friendship !
     */
    public function testYouMustHaveAFriendToUnfriendSomeone () {
        $userDAOService = $this->getUserDAOService();
        $user = $this->newUser('user@localhost.net');

        $userDAOService->save($user);
        $userDAOService->removeFriendship($user, null);
    }
    /**
     * @covers UserDAOService::createFriendship
     * @depends testMakeFriends
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage You can not unfriend yourself !
     */
    public function testCantUnfriendYourself() {
        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser();

        $userDAOService->save($user);
        $userDAOService->removeFriendship($user, $user);
    }

    /**
     * @covers UserDAOService::createFriendship
     * @depends testMakeFriends
     */
    public function testRemoveUser () {

        $userDAOService = $this->getUserDAOService();

        $user = $this->newUser('user@localhost.net');
        $friend = $this->newUser('friend@localhost.net');

        $userDAOService->save($user);
        $userDAOService->save($friend);

        $userDAOService->createFriendship($user, $friend);
        $this->assertEquals(count($user->getFriends()), 1);
        $this->assertEquals(count($friend->getFriends()), 1);

        $this->createGenericPosts($friend, 10);
        $posts = $this->getPostDAOService()->fetchAll();
        $this->assertCount(10, $posts);

        $userDAOService->remove($friend);

        $friend = $userDAOService->findByUsername('friend@localhost.net');
        $this->assertNull($friend);

        $user = $userDAOService->findByUsername('user@localhost.net');
        $this->assertEquals(count($user->getFriends()), 0);

        $posts = $this->getPostDAOService()->fetchAll();
        $this->assertCount(0, $posts);
    }
}