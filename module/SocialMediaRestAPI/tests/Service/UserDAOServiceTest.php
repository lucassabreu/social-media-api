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
        $userDAOService = $this->getServiceManager()->get('SocialMediaRestAPI\Service\UserDAOService');
        $this->assertNotNull($userDAOService);
        $this->assertEquals(get_class($userDAOService), UserDAOService::class);
        return $userDAOService;
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
     */
    public function tesSaveAndRetrieveAUser($uDAO) {
        $user = $this->newUser('lucas.s.abreu@gmail.com', 'Lucas dos Santos Abreu', '123456');
        $user = $uDAO->save($user);

        $this->assertEquals($user->id, 1);
        $this->assertEquals($user->username, 'lucas.s.abreu@gmail.com');
        $this->assertEquals($user->name, 'Lucas dos Santos Abreu');
        $this->assertEquals($user->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')

        $user2 = $uDAO->find(1);

        $this->assertEquals($user2->id, 1);
        $this->assertEquals($user2->username, 'lucas.s.abreu@gmail.com');
        $this->assertEquals($user2->name, 'Lucas dos Santos Abreu');
        $this->assertEquals($user2->password, 'e10adc3949ba59abbe56e057f20f883e'); // md5('123456')

        $user2->name = "Lucas Abreu";
        $user = $uDAO->find(1);
        $this->assertEquals($user2->name, 'Lucas Abreu');
        return $uDAO;
    }
}