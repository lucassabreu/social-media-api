<?php

namespace SocialMediaRestAPITest\Service;

include_once __DIR__ . '/../Traits/UserTestTrait.php';
include_once __DIR__ . '/../Traits/PostTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPI\DAO\PostDAOInterface;
use SocialMediaRestAPI\Service\PostDAOService;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use Core\Model\DAO\Exception\DAOException;
use SocialMediaRestAPITest\Traits\UserTestTrait;
use SocialMediaRestAPITest\Traits\PostTestTrait;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostDAOServiceTest extends TestCase
{
    use UserTestTrait;
    use PostTestTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testHasBeenRegisteredForDi() {
        $postDAOService = $this->getPostDAOService();
        $this->assertNotNull($postDAOService);
        $this->assertEquals(get_class($postDAOService), PostDAOService::class);
        return $postDAOService;
    }
}