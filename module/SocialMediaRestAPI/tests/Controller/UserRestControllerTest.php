<?php

namespace SocialMediaRestAPITest\Controller;

include_once __DIR__ . '/../Traits/ModelHelpTestTrait.php';
include_once __DIR__ . '/../Traits/HttpAuthorizationBasicTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPITest\Traits;
use Zend\View\Model\JsonModel;
use Zend\Http\Request as HttpRequest;

class UserRestControllerTest extends TestCase {

    use Traits\ModelHelpTestTrait;
    use Traits\HttpAuthorizationBasicTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function testRestApiCanBeAccessed()
    {
        $this->dispatch('/api/users');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');
        $this->assertControllerClass('UserRestController');
        $this->assertMatchedRouteName('users-rest');

        $this->dispatch('/api/users/self');

        $this->assertModuleName('SocialMediaRestAPI');
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');
        $this->assertControllerClass('UserRestController');
        $this->assertMatchedRouteName('user-self-rest');
    }

    public function testListUsers() {

        $userDAOService = $this->getUserDAOService();
        $users = [];

        $users[] = $this->newUser('user0@localhost.net');
        $users[] = $this->newUser('user1@localhost.net');
        $users[] = $this->newUser('user2@localhost.net');
        $users[] = $this->newUser('user3@localhost.net');
        $users[] = $this->newUser('user4@localhost.net');

        foreach($users as $u)
            $userDAOService->save($u);

        $this->dispatch('/api/users');
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(5, $vars['result']);

        // values
        $this->assertInternalType('array', $vars['result'][0]);
        $this->assertArrayHasKey('id', $vars['result'][0]);
        $this->assertArrayHasKey('name', $vars['result'][0]);

        // should not show
        $this->assertArrayNotHasKey('username', $vars['result'][0]);
        $this->assertArrayNotHasKey('password', $vars['result'][0]);

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(5, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(5, $vars['paging']['total']);
    }

    /**
     * @depends testListUsers
     */
    public function testGetListWithPagination () {

        $totalToCreate = 103;
        $users = $this->createGenericUsers($totalToCreate);

        // page 1
        $this->dispatch('/api/users');
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

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
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // page 2
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'offset' => 50
        ));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

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
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // page 3
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'offset' => 100
        ));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount($totalToCreate % 50, $vars['result']); // only 3

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals($totalToCreate % 50, $vars['paging']['count']); // only 3
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // limit = 10
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'limit' => 10,
            'offset' => 0
        ));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(10, $vars['result']); // only 3

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(10, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // limit = 10
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'limit' => 10,
            'offset' => 100 // last page
        ));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(3, $vars['result']); // only 3

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(3, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(100, $vars['paging']['offset']); // last page
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // limit = 10 & offset = 200
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'limit' => 10,
            'offset' => 200
        ));
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(0, $vars['result']); // only 3

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(200, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals($totalToCreate, $vars['paging']['total']);

        // cant page more than 50 items
        $this->dispatch('/api/users', HttpRequest::METHOD_GET, array(
            'limit' => 100,
        ));
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals("Maximum limit is 50, parameter used was 100", $vars['error']['message']);
    }

    /**
     * @depends testListUsers
     */
    public function testGetListWithFilter () {
        $users = $this->createGenericUsers(50);

        $userDAOService = $this->getUserDAOService();

        for($i = 1; $i <= 5; $i++)
            $users[] = $userDAOService->save(
                $this->newUser("outro$i@localhost.net",
                               "Joãozinho $i da Silva"));

        $this->dispatch('/api/users', HttpRequest::METHOD_GET, [
            'q' => 'name:Silva'
        ]);
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertCount(5, $vars['result']);

        foreach($vars['result'] as $user) {
            $this->assertRegExp("/Joãozinho [0-9] da Silva/", $user['name']);
        }

        // pagination
        $this->assertArrayHasKey('paging', $vars);
        $this->assertArrayHasKey('count', $vars['paging']);
        $this->assertEquals(5, $vars['paging']['count']);
        $this->assertArrayHasKey('offset', $vars['paging']);
        $this->assertEquals(0, $vars['paging']['offset']);
        $this->assertArrayHasKey('total', $vars['paging']);
        $this->assertEquals(5, $vars['paging']['total']);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCantKnowYourselfIfYouDontTellMeWhoAreYou() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));
        $this->dispatch('/api/users/self');
        $this->assertResponseStatusCode(401);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCanKnowYourself() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/self');
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('name', $vars['result']);
        $this->assertArrayHasKey('username', $vars['result']);
        $this->assertArrayNotHasKey('password', $vars['result']);

        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('Lucas dos Santos Abreu', $vars['result']['name']);
        $this->assertEquals('lucas.s.abreu@gmail.com', $vars['result']['username']);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testGetOneUser() {

        // init data
        $userDAOService = $this->getUserDAOService();
        $users = $this->createGenericUsers(5);
        $user = $userDAOService->save($this->newUser('joao@localhost.net', 'Joãozinho'));

        $this->dispatch('/api/users/6');
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('name', $vars['result']);
        $this->assertArrayNotHasKey('username', $vars['result']);
        $this->assertArrayNotHasKey('password', $vars['result']);

        $this->assertEquals(6, $vars['result']['id']);
        $this->assertEquals('Joãozinho', $vars['result']['name']);

        $this->dispatch('/api/users/100');
        $this->assertResponseStatusCode(404);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

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
    public function testCreateANewUser() {

        $this->dispatch('/api/users', HttpRequest::METHOD_POST, [
            'name' => 'Lucas dos Santos Abreu',
            'username' => 'lucas.s.abreu@gmail.com',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCode(201);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('name', $vars['result']);
        $this->assertArrayNotHasKey('password', $vars['result']);
        $this->assertArrayNotHasKey('username', $vars['result']);

        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('Lucas dos Santos Abreu', $vars['result']['name']);

        $this->dispatch('/api/users', HttpRequest::METHOD_POST, [
            'name' => 'Joãozinho',
            'username' => 'joao@localhost.net',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCode(201);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertEquals(2, $vars['result']['id']);
        $this->assertEquals('Joãozinho', $vars['result']['name']);

        // again?
        $this->dispatch('/api/users', HttpRequest::METHOD_POST, [
            'name' => 'Joãozinho',
            'username' => 'joao@localhost.net',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCode(403);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertEquals('Aready exists a User with the username "joao@localhost.net"', $vars['error']['message']);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCantChangeTheNameWithoutAuthorization() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));

        $this->dispatch('/api/users/1', HttpRequest::METHOD_PUT, [
            'name' => 'Lucas Abreu',
            'username' => 'joao@localhost.net',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCode(401);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCanChangeTheName() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));
    
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1', HttpRequest::METHOD_PUT, [
            'name' => 'Lucas Abreu',
            'username' => 'joao@localhost.net',
            'password' => '123456',
        ]);
        $this->assertResponseStatusCode(200);
        $this->assertControllerName('SocialMediaRestAPI\Controller\UserRest');

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('id', $vars['result']);
        $this->assertArrayHasKey('name', $vars['result']);
        $this->assertArrayNotHasKey('username', $vars['result']);
        $this->assertArrayNotHasKey('password', $vars['result']);

        $this->assertEquals(1, $vars['result']['id']);
        $this->assertEquals('Lucas Abreu', $vars['result']['name']);
        
        $user = $userDAOService->findById(1);
        $this->assertEquals('lucas.s.abreu@gmail.com', $user->username);
        $this->assertEquals('e10adc3949ba59abbe56e057f20f883e', $user->password); // md5(123456)

        // without name
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1', HttpRequest::METHOD_PUT, [
            'name' => '',
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        $this->assertRegExp("/Invalid input: name = ''\. .+/", $vars['error']['message']);

        $this->createGenericUsers(1);

        // update other user
        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/2', HttpRequest::METHOD_PUT, [
            'name' => 'Joãozinho',
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
    public function testCanChangePassword() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1/change-password', HttpRequest::METHOD_PUT, [
            'password' => '123456',
            'newPassword' => '654321',
        ]);
        $this->assertResponseStatusCode(200);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayHasKey('result', $vars);
        $this->assertArrayHasKey('success', $vars['result']);
        
        $this->assertEquals('true', $vars['result']['success']);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCanChangePasswordMayShowErrors() {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1/change-password', HttpRequest::METHOD_PUT, [
            'password' => 'errado',
            'newPassword' => '654321',
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals("Password is not correct !", $vars['error']['message']);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1/change-password', HttpRequest::METHOD_PUT, [
            'newPassword' => '654321',
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals("Password is not correct !", $vars['error']['message']);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1/change-password', HttpRequest::METHOD_PUT, [
            'password' => '654321',
        ]);
        $this->assertResponseStatusCode(403);

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals("Must be informmed a new password !", $vars['error']['message']);

        $this->createGenericUsers(1);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/2/change-password', HttpRequest::METHOD_PUT);
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
    public function testCantDeleteUserWithoutAuthorization () {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));

        $this->dispatch('/api/users/1', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(401);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testDeleteUser () {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/1', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(204);

        $user = $userDAOService->findById(1);
        $this->assertNull($user);
    }

    /**
     * @depends testRestApiCanBeAccessed 
     */
    public function testCantDeleteOtherUser () {
        $userDAOService = $this->getUserDAOService();
        $user = $userDAOService->save($this->newUser('lucas.s.abreu@gmail.com',
                                                     'Lucas dos Santos Abreu',
                                                     '123456'));
        $this->createGenericUsers(1);

        $this->setAuthorizationHeader('lucas.s.abreu@gmail.com', '123456');
        $this->dispatch('/api/users/2', HttpRequest::METHOD_DELETE);
        $this->assertResponseStatusCode(403);   

        $viewModel = $this->getViewModel();
        $this->assertEquals(get_class($viewModel), JsonModel::class);
        $vars = $viewModel->getVariables();

        $this->assertArrayNotHasKey('result', $vars);
        $this->assertArrayHasKey('error', $vars);
        $this->assertArrayHasKey('message', $vars['error']);
        
        $this->assertEquals("You can't modify others users data !", $vars['error']['message']); 
    }
}
