<?php

namespace SocialMediaRestAPITest\Service;

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

}