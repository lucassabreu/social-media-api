<?php

namespace SocialMediaRestAPITest\DAO;

use Core\Test\TestCase;
use Zend\StdLib\ArrayUtils;
use SocialMediaRestAPI\DAO\UserDAOInterface;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Model\Entity\User;

class UserDAOServiceTest extends TestCase 
{
    public function setUp()
    {
        $this->setApplicationConfig(
            ArrayUtils::merge(
                include __DIR__ . '/../../../../config/application.config.php',
                \Bootstrap::getTestConfig()
            )
        );
        parent::setUp();
    }

    public function testHasBeenRegisteredForDI() {
        $userDAOService = $this->getServiceManager()->get('SocialMediaRestAPI\Service\UserDAOService');
        $this->assertNotNull($userDAOService);
        $this->assertEquals(get_class($userDAOService), UserDAOService::class);
    }

    /**
     * @convers User::getInputFilter
     * @convers User::__set
     * @convers User::__get
     */
    public function testUserInputFilterFilters() {
        $user = new User();

        $if = $user->getInputFilter();

        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('username'));
        $this->assertTrue($if->has('password'));
        $this->assertTrue($if->has('name'));

        $user->id = '01.0';
        $this->assertEquals($user->id, 1);
        $user->username = "   <b>lucas.s.abreu@gmail.com</b> ";
        $this->assertEquals($user->username, 'lucas.s.abreu@gmail.com');
        $user->password = "  123 ";
        $this->assertEquals($user->password, '123');
        $user->name = " <i>Lucas</i> dos Santos Abreu   ";
        $this->assertEquals($user->name, 'Lucas dos Santos Abreu');
    }

    /**
     * @convers User::setData
     * @convers User::exchangeArray
     * @convers User::getData
     * @convers User::getArrayCopy
     * @convers User::toArray
     */
    public function testGetSetDataIntoUser () {

        $values = [
            'id' => '01.0',
            'username' => '   <b>lucas.s.abreu@gmail.com</b> ',
            'password' => '  123 ',
            'name' => ' <i>Lucas</i> dos Santos Abreu   ',
        ];

        $user = new User();
        $user->setData($values);
        $data = $user->getData();
        $this->validateUserDataLucas($data);

        $user = new User();
        $user->exchangeArray($values);
        $data = $user->toArray();
        $this->validateUserDataLucas($data);

        $data = $user->getArrayCopy();
        $this->validateUserDataLucas($data);

        return $user;
    }

    private function validateUserDataLucas ($data) {
        $this->assertEquals($data['id'], 1);
        $this->assertEquals($data['username'], 'lucas.s.abreu@gmail.com');
        $this->assertEquals($data['password'], '123');
        $this->assertEquals($data['name'], 'Lucas dos Santos Abreu');
    }

    /**
     * @depends testGetSetDataIntoUser
     * @convers User::validate
     */
    public function testCanValidateAUser($user) {
        $this->assertTrue($user->validate());
    }

}