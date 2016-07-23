<?php

namespace SocialMediaRestAPITest\DAO;

use Core\Test\TestCase;
use Zend\StdLib\ArrayUtils;
use SocialMediaRestAPI\DAO\UserDAOInterface;

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
        $userDAOService = $this->getService('SocialMediaRestAPI\DAO\UserDAOService');
        $this->assertNotNull($userDAOService);
        $this->assertTrue($userDAOService instanceof UserDAOInterface);
    }

}