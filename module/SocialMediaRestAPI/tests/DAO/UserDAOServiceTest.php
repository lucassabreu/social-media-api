<?php

namespace SocialMediaRestAPITest\DAO;

use Core\Test\TestCase;
use Zend\StdLib\ArrayUtils;
use SocialMediaRestAPI\DAO\UserDAOInterface;
use SocialMediaRestAPI\Service\UserDAOService;
use SocialMediaRestAPI\Model\Entity\User;
use Core\Model\DAO\Exception\DAOException;

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

    public function testHasBeenRegisteredForDi() {
        $userDAOService = $this->getServiceManager()->get('SocialMediaRestAPI\Service\UserDAOService');
        $this->assertNotNull($userDAOService);
        $this->assertEquals(get_class($userDAOService), UserDAOService::class);
    }

    /**
     * @convers User::getInputFilter
     * @convers User::__set
     * @convers User::__get
     * @convers User::valid
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
     * @convers User::valid
     */
    public function testGetAndSetDataIntoUser () {

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
     * @depends testGetAndSetDataIntoUser
     * @convers User::valid
     * @convers User::validate
     */
    public function testCanValidateAUser($user) {
        $this->assertTrue($user->validate());
        return $user;
    }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testCannotSetAUsernameThatIsNotAEmail ($user) {
         $user->username = "joaozinho da silva";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testEmailForUsernameMustBeComplete ($user) {
         $user->username = "joaozinho@";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testEmailForUsernameMustBeOnly100AtLength ($user) {
         $user->username = "joaozinhotemumenderecodeemailqueehgrandedemaisenaofazsentidoaquiaindaehcurtoprecisodenovasideiasdetextoipsum@localhost.com";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testUsernameMustBeInformmed ($user) {
         $user->username = "   ";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testUsernameMustBeInformmedAndNotNull ($user) {
         $user->username = null;
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testPasswordMustBeInformmed ($user) {
         $user->password = "   ";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testPasswordMustBeInformmedAndNotNull ($user) {
         $user->password = null;
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testNameMustBeInformmed ($user) {
         $user->name = "   ";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testNameMustBeInformmedAndNotNull ($user) {
         $user->name = null;
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     * @expectedException DAOException
     */
     public function testNameMustBeOnly150AtLength ($user) {
         $user->username = "Joãozinho tem um nome bem comprido porque alguem tentou trocar o tabeliao por um programa de reconhecimento de voz e não desligaram na hora certa, faltou um pouco";
     }

    /**
     * @depends testCanValidateAUser
     * @convers User::valid
     * @convers User::validate
     */
     public function testIdCanBeNull ($user) {
         $user->id = null;
         $this->assertNull($user->id);
     }

}