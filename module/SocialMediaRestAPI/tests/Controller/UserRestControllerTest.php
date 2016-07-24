<?php

namespace SocialMediaAPITest\Controller;

use Core\Test\TestCase;

class UserRestControllerTest extends TestCase {

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testListActionCanBeAccessed()
    {
        $this->dispatch('/api/users');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('SocialMediaAPI');
        $this->assertControllerName('SocialMediaAPI\Controller\UserRestController');
        $this->assertControllerClass('UserRestController');
        $this->assertMatchedRouteName('users-rest');
    }

}
