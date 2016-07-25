<?php

namespace SocialMediaRestAPITest\Controller;

include_once __DIR__ . '/../Traits/UserTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPITest\Traits\UserTestTrait;
use Zend\View\Model\JsonModel;
use Zend\Http\Request as HttpRequest;

class FriendRestControllerTest extends TestCase {

    use UserTestTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testRestAPICanBeAccessed()
    {
        $this->createGenericUsers(1);

        $this->dispatch('/api/users/1/friends');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\FriendRest');
        $this->assertControllerClass('FriendRestController');
        $this->assertMatchedRouteName('user-friends-rest');
    }

    /**
     * @depends testRestAPICanBeAccessed 
     */
    public function testCreateAFriendship() {
        $users = $this->createGenericUsers(2);
        $userDAOService = $this->getUserDAOService();

        $this->dispatch('/api/users/1/friends', HttpRequest::METHOD_POST, [
            'id' => 2,
        ]);
        $this->assertResponseStatusCode(201);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('name', $vars['result']);
        
        $this->assertEquals(2, $vars['result']['id']);
        $this->assertEquals('Usuário 2', $vars['result']['id']);

        $user = $userDAOService->findById(1);
        $this->assertCount(1, $user->getFriends());
        $this->assertEquals(2, $user->getFriends()[0]->id);

        // again?
        $this->dispatch('/api/users/1/friends', HttpRequest::METHOD_POST, [
            'id' => 2,
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);

        $this-assertRegExp('/You and \".+\" are aready friends \!/', $vars['error']['message']);

        $this->dispatch('/api/users/1/friends', HttpRequest::METHOD_POST, [
            'id' => 1,
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);

        $this-assertEquals("You can not befriend yourself !", $vars['error']['message']);

        $this->dispatch('/api/users/1/friends', HttpRequest::METHOD_POST);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);

        $this-assertEquals("Must be informmed the two users to create a friendship !", 
            $vars['error']['message']);
    }

    /**
     * @depends testRestAPICanBeAccessed 
     */
    public function testListFriends() {
        $users = $this->createGenericUsers(3);
        $userDAOService = $this->getUserDAOService();

        $userDAOService->createFriendship($users[0], $users[1]);
        
        $this->dispatch('/api/users/1/friends');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(1, $vars['result']);

        $this->assertArrayHasKey('id', $vars['result'][0]);
        $this->assertArrayHasKey('name', $vars['result'][0]);

        // user 2
        $this->assertEquals($users[1]->id, $vars['result'][0]['id']);
        $this->assertEquals($users[1]->name, $vars['result'][0]['name']);
        
        $this->dispatch('/api/users/2/friends');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(1, $vars['result']);

        $this->assertArrayHasKey('id', $vars['result'][0]);
        $this->assertArrayHasKey('name', $vars['result'][0]);

        // user 1
        $this->assertEquals($users[0]->id, $vars['result'][0]['id']);
        $this->assertEquals($users[0]->name, $vars['result'][0]['name']);
        
        $this->dispatch('/api/users/3/friends');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(0, $vars['result']);

        $this->dispatch('/api/users/4/friends'); // not exist
        $this->assertResponseStatusCode(404);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);

        $this->assertEquals('User 4 does not exist !', $vars['error']['message']);
    }

    public function testCanUnfriendUsers() {
        $users = $this->createGenericUsers(2);
        $userDAOService = $this->getUserDAOService();

        $userDAOService->createFriendship($users[0], $users[1]);

        $this->dispatch('/api/users/1/friends/2', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(204);

        $user = $userDAOService->findById(1);
        $this->assertCount(0, $user->getFriends());

        $user = $userDAOService->findById(2);
        $this->assertCount(0, $user->getFriends());
    }
}