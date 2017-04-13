<?php

namespace SocialMediaRestAPITest\Controller;

include_once __DIR__ . '/../Traits/ModelHelpTestTrait.php';
include_once __DIR__ . '/../Traits/HttpAuthorizationBasicTrait.php';

use DateTime;
use Core\Test\TestCase;
use SocialMediaRestAPITest\Traits;
use Zend\View\Model\JsonModel;
use Zend\Http\Request as HttpRequest;

class PostRestControllerTest extends TestCase
{
    use Traits\ModelHelpTestTrait;
    use Traits\HttpAuthorizationBasicTrait;

    public function setUp()
    {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testRestApiCanBeAccessed()
    {
        $this->dispatch('/api/posts');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\PostRest');
        $this->assertControllerClass('PostRestController');
        $this->assertMatchedRouteName('posts-rest');

        $this->dispatch('/api/posts/user/1');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\PostRest');
        $this->assertControllerClass('PostRestController');
        $this->assertMatchedRouteName('posts-user-rest');

        $this->dispatch('/api/feed');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\PostRest');
        $this->assertControllerClass('PostRestController');
        $this->assertMatchedRouteName('posts-feed-rest');
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanCreateAPost()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $this->getUserDAOService()->save($user);

        $this->dispatch('/api/posts', HttpRequest::METHOD_POST, [
            'text' => 'something funny'
        ]);
        $this->assertResponseStatusCode(401);

        $now = new DateTime('now');
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/posts', HttpRequest::METHOD_POST, [
            'text' => 'something funny'
        ]);
        $this->assertResponseStatusCode(201);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('text', $vars['result']);
        $this->assertArrayHasKey('datePublish', $vars['result']);
        $this->assertArrayHasKey('userId', $vars['result']);

        $this->assertEquals(1, $vars['result']['userId']);
        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('something funny', $vars['result']['text']);
        $this->assertTrue($now->format('Y-m-d H:i:s') <= $vars['result']['datePublish']);

        $post = $postDAOService->findById(1);
        $this->assertNotNull($post);
        $this->assertEquals(1, $post->user->id);
        $this->assertEquals($vars['result']['datePublish'], $post->datePublish->format('Y-m-d H:i:s'));
        $this->assertEquals("something funny", $post->text);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCantUpdateAPostWithoutAuthentication()
    {
        $this->dispatch('/api/posts/1', HttpRequest::METHOD_PUT, [
            'text' => 'not that funny'
        ]);
        $this->assertResponseStatusCode(401);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanUpdateAPost()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $this->getUserDAOService()->save($user);

        $post = $this->newPost('something funny',
                               $user,
                               '2016-07-28 00:00:00');
        $postDAOService->save($post);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/posts/1', HttpRequest::METHOD_PUT, [
            'text' => 'not that funny'
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('text', $vars['result']);
        $this->assertArrayHasKey('datePublish', $vars['result']);
        $this->assertArrayHasKey('userId', $vars['result']);

        $this->assertEquals(1, $vars['result']['userId']);
        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('not that funny', $vars['result']['text']);
        $this->assertEquals('2016-07-28 00:00:00', $vars['result']['datePublish']);

        $post = $postDAOService->findById(1);
        $this->assertNotNull($post);
        $this->assertEquals(1, $post->user->id);
        $this->assertEquals('2016-07-28 00:00:00', $post->datePublish->format('Y-m-d H:i:s'));
        $this->assertEquals("not that funny", $post->text);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCantUpdateOthersUsersPosts()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->getUserDAOService()->save(
            $this->newUser('lucas.s.abreu@gmail.com',
                           'Lucas dos Santos Abreu',
                           '123456'));

        $post = $this->newPost('something funny',
                               $user,
                               '2016-07-28 00:00:00');
        $postDAOService->save($post);

        $user = $this->getUserDAOService()->save(
            $this->newUser('joaozinho@localhost.net',
                           'Joãozinho',
                           '123456'));

        $this->setAuthorizationHeader('joaozinho@localhost.net', '123456');
        $this->dispatch('/api/posts/1', HttpRequest::METHOD_PUT, [
            'text' => 'not that funny'
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("You can't modify others users data !", $vars['error']['message']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanViewAPost()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $this->getUserDAOService()->save($user);

        $post = $this->newPost('something funny',
                               $user,
                               '2016-07-28 00:00:00');
        $postDAOService->save($post);

        $this->dispatch('/api/posts/1');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('text', $vars['result']);
        $this->assertArrayHasKey('datePublish', $vars['result']);
        $this->assertArrayHasKey('userId', $vars['result']);

        $this->assertEquals(1, $vars['result']['userId']);
        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('something funny', $vars['result']['text']);
        $this->assertEquals('2016-07-28 00:00:00', $vars['result']['datePublish']);

        $this->dispatch('/api/posts/2');
        $this->assertResponseStatusCode(404);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals('Post 2 does not exist !', $vars['error']['message']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanRemoveAPost()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $this->getUserDAOService()->save($user);

        $post = $this->newPost('something funny',
                               $user,
                               '2016-07-28 00:00:00');
        $postDAOService->save($post);

        $this->dispatch('/api/posts/1', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(401);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/posts/1', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(204);

        $post = $postDAOService->findById(1);
        $this->assertNull($post);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCantDeleteAPostWithoutAuthentication()
    {
        $postDAOService = $this->getPostDAOService();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $this->getUserDAOService()->save($user);

        $post = $this->newPost('something funny',
                               $user,
                               '2016-07-28 00:00:00');
        $postDAOService->save($post);

        $user = $this->newUser('joaozinho@localhost.net',
                               'Joãozinho',
                               '123456');
        $this->getUserDAOService()->save($user);

        $this->setAuthorizationHeader('joaozinho@localhost.net', '123456');
        $this->dispatch('/api/posts/1', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("You can't modify others users data !", $vars['error']['message']);
    }

    private function loadBasicDataPosts()
    {
        $postDAOService = $this->getPostDAOService();

        $users = $this->createGenericUsers(3);

        $posts = [];
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post with cheese", $users[1], "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $users[2], "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $users[1], "2016-07-01 18:00:00");
        $posts[] = $this->newPost("other post with cheese", $users[0], "2016-07-02 12:00:00");
        $posts[] = $this->newPost("a post", $users[2], "2016-07-02 12:00:00");

        foreach ($posts as $post) {
            $postDAOService->save($post);
        }

        return $users;
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanListPostsWithFilter()
    {
        $this->loadBasicDataPosts();
        $postDAOService = $this->getPostDAOService();

        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'q' => 'text:cheese'
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(2, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['total']);

        foreach ($vars['result'] as $post) {
            $this->assertArrayHasKey('id', $post);
            $this->assertArrayHasKey('userId', $post);
            $this->assertArrayHasKey('datePublish', $post);
            $this->assertArrayHasKey('text', $post);
            $this->assertRegExp("/cheese/i", $post['text']);
        }

        // order
        $this->assertEquals("2016-07-02 12:00:00", $vars['result'][0]['datePublish']);
        $this->assertEquals("2016-07-01 11:00:00", $vars['result'][1]['datePublish']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanListPostsWithoutFilter()
    {
        $users = $this->loadBasicDataPosts();
        $postDAOService = $this->getPostDAOService();
        
        $this->createGenericPosts($users[0], 100);

        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, []);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(106, $vars['paging']['total']);

        // page 2
        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'offset' => 50
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(106, $vars['paging']['total']);

        // page 3
        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'offset' => 100
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(6, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(6, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(106, $vars['paging']['total']);

        // page 1 limit
        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'limit' => 10,
            'offset' => 0
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(10, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(10, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(106, $vars['paging']['total']);

        // page 10 limit
        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'offset' => 100,
            'limit' => 10
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(6, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(6, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(106, $vars['paging']['total']);
        
        // too much on limit
        $this->dispatch('/api/posts', HttpRequest::METHOD_GET, [
            'limit' => 100
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("Maximum limit is 50, parameter used was 100", $vars['error']['message']);
    }

    private function loadDataListPostsFromUser()
    {
        $postDAOService = $this->getPostDAOService();

        $users = $this->createGenericUsers(3);

        $posts = [];
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post with cheese", $users[1], "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post with cream cheese", $users[1], "2016-07-03 11:00:00");
        $posts[] = $this->newPost("a post", $users[2], "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $users[1], "2016-07-01 18:00:00");
        $posts[] = $this->newPost("other post with cheese", $users[0], "2016-07-02 12:00:00");

        foreach ($posts as $post) {
            $postDAOService->save($post);
        }

        return $users;
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanListPostsFromAUser()
    {
        $this->loadDataListPostsFromUser();

        $this->dispatch('/api/posts/user/2', HttpRequest::METHOD_GET, [
            'q' => 'text:cheese'
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(2, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['total']);

        foreach ($vars['result'] as $post) {
            $this->assertArrayHasKey('id', $post);
            $this->assertArrayHasKey('userId', $post);
            $this->assertArrayHasKey('datePublish', $post);
            $this->assertArrayHasKey('text', $post);
            $this->assertEquals(2, $post['userId']);
            $this->assertRegExp("/cheese/i", $post['text']);
        }

        // order
        $this->assertEquals("2016-07-03 11:00:00", $vars['result'][0]['datePublish']);
        $this->assertEquals("2016-07-01 11:00:00", $vars['result'][1]['datePublish']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanListPostsFromAUserWithoutFilter()
    {
        $users = $this->loadDataListPostsFromUser();

        $this->createGenericPosts($users[0], 100);

        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, []);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        foreach ($vars['result'] as $post) {
            $this->assertEquals(1, $post['userId']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(102, $vars['paging']['total']);

        // page 2
        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, [
            'offset' => 50
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        foreach ($vars['result'] as $post) {
            $this->assertEquals(1, $post['userId']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(102, $vars['paging']['total']);

        // page 3
        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, [
            'offset' => 100
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(2, $vars['result']);

        foreach ($vars['result'] as $post) {
            $this->assertEquals(1, $post['userId']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(102, $vars['paging']['total']);

        // page 1 limit
        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, [
            'limit' => 10,
            'offset' => 0,
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(10, $vars['result']);

        foreach ($vars['result'] as $post) {
            $this->assertEquals(1, $post['userId']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(10, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(102, $vars['paging']['total']);

        // page 10 limit
        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, [
            'offset' => 95,
            'limit' => 10
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(7, $vars['result']);

        foreach ($vars['result'] as $post) {
            $this->assertEquals(1, $post['userId']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(7, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(95, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(102, $vars['paging']['total']);
        
        // too much on limit
        $this->dispatch('/api/posts/user/1', HttpRequest::METHOD_GET, [
            'limit' => 100,
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("Maximum limit is 50, parameter used was 100", $vars['error']['message']);
        
        // it does not exist
        $this->dispatch('/api/posts/user/100', HttpRequest::METHOD_GET, []);
        $this->assertResponseStatusCode(404);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals('User 100 does not exist !', $vars['error']['message']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCantSeeTheFeedWithoutAuthorization()
    {
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET);
        $this->assertResponseStatusCode(401);
    }

    public function loadDataSeeTheFeed()
    {
        $postDAOService = $this->getPostDAOService();
        $userDAOService = $this->getUserDAOService();

        $users = $this->createGenericUsers(2);

        $users[] = $this->newUser('lucas.s.abreu@gmail.com',
                                  'Lucas dos Santos Abreu',
                                  '123456');
        $this->getUserDAOService()->save($users[2]);

        $userDAOService->createFriendship($users[0], $users[2]);
        $userDAOService->createFriendship($users[1], $users[0]);

        $posts = [];

        // user 1
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 13:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 18:00:00");
        $posts[] = $this->newPost("a post with cream cheese", $users[0], "2016-07-01 01:00:00");

        // user 2
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 12:00:00");

        // user 3
        $posts[] = $this->newPost("a post", $users[2], "2016-07-02 12:00:00");
        $posts[] = $this->newPost("a post with cheese", $users[2], "2016-07-03 18:00:00");

        foreach ($posts as $post) {
            $postDAOService->save($post);
        }

        return $users;
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanSeeTheFeed()
    {
        $this->loadDataSeeTheFeed();

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'q' => 'text:cheese'
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(2, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(2, $vars['paging']['total']);

        foreach ($vars['result'] as $post) {
            $this->assertArrayHasKey('id', $post);
            $this->assertArrayHasKey('userId', $post);
            $this->assertArrayHasKey('datePublish', $post);
            $this->assertArrayHasKey('text', $post);
            $this->assertRegExp("/cheese/i", $post['text']);
        }

        // order
        $this->assertEquals("2016-07-03 18:00:00", $vars['result'][0]['datePublish']);
        $this->assertEquals(3, $vars['result'][0]['userId']);
        $this->assertEquals("2016-07-01 01:00:00", $vars['result'][1]['datePublish']);
        $this->assertEquals(1, $vars['result'][1]['userId']);
    }

    /**
     * @depends testRestApiCanBeAccessed
     */
    public function testCanSeeTheFeedWithoutFilter()
    {
        $users = $this->loadDataSeeTheFeed();
        $this->createGenericPosts($users[2], 100);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(107, $vars['paging']['total']); // 100 + 5 + 2

        // page 2
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'offset' => 50
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(50, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(50, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(107, $vars['paging']['total']);

        // page 3
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'offset' => 100,
            'limit' => 10
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(7, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(7, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(107, $vars['paging']['total']);

        // page 1 limit
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'limit' => 10,
            'offset' => 0
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(10, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(10, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(107, $vars['paging']['total']);

        // page 20 limit
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'offset' => 95,
            'limit' => 20
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(12, $vars['result']);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(12, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(95, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(107, $vars['paging']['total']);
        
        // too much on limit
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/feed', HttpRequest::METHOD_GET, [
            'limit' => 100
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("Maximum limit is 50, parameter used was 100", $vars['error']['message']);
    }
}
