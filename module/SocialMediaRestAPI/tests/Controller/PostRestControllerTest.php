<?php

namespace SocialMediaRestAPITest\Controller;

include_once __DIR__ . '/../Traits/PostTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPITest\Traits\PostTestTrait;
use Zend\View\Model\JsonModel;
use Zend\Http\Request as HttpRequest;

class PostRestControllerTest extends TestCase {
    use PostTestTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testRestAPICanBeAccessed()
    {
        $this->dispatch('/api/posts');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\PostRest');
        $this->assertControllerClass('PostRestController');
        $this->assertMatchedRouteName('posts-rest');

        $this->dispatch('/api/posts/feed');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\PostRest');
        $this->assertControllerClass('PostRestController');
        $this->assertMatchedRouteName('posts-feed-rest');
    }


}