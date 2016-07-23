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
     */
    public function testInputFilterFilters() {
        $user = new User();

        $if = $user->getInputFilter();

        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('username'));
        $this->assertTrue($if->has('password'));
        $this->assertTrue($if->has('name'));

        $user->id = '01';
        $this->assertEquals($user->id, 1);
        $user->username = "   <b>lucas.s.abreu@gmail.com</b> ";
        $this->assertEquals($user->username, 'lucas.s.abreu@gmail.com');
        $user->password = "  123 ";
        $this->assertEquals($user->password, '123');
        $user->name = " <i>Lucas</i> dos Santos Abreu   ";
        $this->assertEquals($user->name, 'Lucas dos Santos Abreu');
    }

}