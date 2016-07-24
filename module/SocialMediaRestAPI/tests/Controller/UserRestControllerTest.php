<?php

namespace SocialMediaRestAPITest\Controller;

use Core\Test\TestCase;
use Core\Test\RestAPITestTrait;

class UserRestControllerTest extends TestCase {

    use RestAPITestTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testGetListCanBeAccessed()
    {
        $this->dispatch('/api/users');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');
        $this->assertControllerClass('UserRestController');
        $this->assertMatchedRouteName('users-rest');
    }

}
