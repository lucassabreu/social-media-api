<?php

namespace SocialMediaRestAPITest\Service;

include_once __DIR__ . '/../Traits/PostTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPI\DAO\PostDAOInterface;
use SocialMediaRestAPI\Service\PostDAOService;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use Core\Model\DAO\Exception\DAOException;
use SocialMediaRestAPITest\Traits\PostTestTrait;
use Zend\Paginator\Adapter\AdapterInterface;

/**
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class PostDAOServiceTest extends TestCase
{
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

    /**
     * @covers PostDAOService::save
     * @covers PostDAOService::findById
     * @depends testHasBeenRegisteredForDi
     */
    public function testCanCreateAndRetrieve() {
        $postDAOService = $this->getPostDAOService();

        $user = $this->createGenericUsers(1)[0];
        $post = $this->newPost('something to say', $user, "2016-07-02 09:30:00");

        $post = $postDAOService->save($post);

        $this->assertEquals($post->id, 1);
        $this->assertNotNull($post->user);
        $this->assertEquals($post->user->name, $user->name);
        $this->assertNotNull($post->datePublish);
        $this->assertEquals($post->datePublish->format("Y-m-d H:i:s"), '2016-07-02 09:30:00');
        $this->assertEquals($post->text, 'something to say');

        $post = $postDAOService->findById(1);

        $this->assertEquals($post->id, 1);
        $this->assertNotNull($post->user);
        $this->assertEquals($post->user->name, $user->name);
        $this->assertNotNull($post->datePublish);
        $this->assertEquals($post->datePublish->format("Y-m-d H:i:s"), '2016-07-02 09:30:00');
        $this->assertEquals($post->text, 'something to say');

        $post->text = "i changed my mind";
        $postDAOService->save($post);

        $post = $postDAOService->findById(1);

        $this->assertEquals($post->text, "i changed my mind");
    }

    /**
     * @covers PostDAOService::save
     * @depends testCanCreateAndRetrieve
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Publish date cannot be changed !
     */
    public function testCannotChangeDatePublish() {
        $post = $this->newPost('something funny', null, "2016-07-07 12:00:01");
        $postDAOService = $this->getPostDAOService();
        $post = $postDAOService->save($post);

        $post->datePublish = "2016-07-01 12:00:02";

        $post = $postDAOService->save($post);
    }

    /**
     * @covers PostDAOService::save
     * @depends testCanCreateAndRetrieve
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage User cannot be changed !
     */
    public function testCannotChangeUser() {
        $users = $this->createGenericUsers(2);
        $post = $this->newPost('something funny', $users[0]);
        $postDAOService = $this->getPostDAOService();
        $post = $postDAOService->save($post);

        $post->user = $users[1];

        $post = $postDAOService->save($post);
    }

    /**
     * @covers PostDAOService::save
     * @depends testCanCreateAndRetrieve
     */
    public function testCanRemoveAPost() {
        $user = $this->createGenericUsers(1)[0];
        $post = $this->createGenericPosts($user, 1)[0];
        $this->assertEquals(1, $post->id);

        $postDAOService = $this->getPostDAOService();
        $postDAOService->remove($post);

        $post2 = $postDAOService->findById(1);
        $this->assertNull($post2);
    }

    /**
     * @covers PostDAOService::fetchUserPosts
     * @depends testCanCreateAndRetrieve
     */
    public function testCanGetAUsersPost() {
        $userDAOService = $this->getUserDAOService();
        $postDAOService = $this->getPostDAOService();

        $user = $this->createGenericUsers(1)[0];

        $posts = [];
        $posts[] = $this->newPost("a post", $user, "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-01 18:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-02 12:00:00");

        foreach($posts as $post)
            $postDAOService->save($post);
        
        $uPosts = $postDAOService->fetchUserPosts($user);
        $this->assertCount(5, $uPosts);

        $this->assertEquals("2016-07-03 13:00:00", $uPosts[0]->datePublish);
        $this->assertEquals("2016-07-02 12:00:00", $uPosts[1]->datePublish);
        $this->assertEquals("2016-07-01 18:00:00", $uPosts[2]->datePublish);
        $this->assertEquals("2016-07-01 12:00:00", $uPosts[3]->datePublish);
        $this->assertEquals("2016-07-01 11:00:00", $uPosts[4]->datePublish);

        $uPosts = $postDAOService->fetchUserPosts($user, 3, 1);
        $this->assertCount(3, $uPosts);

        // first
        $this->assertEquals("2016-07-02 12:00:00", $uPosts[0]->datePublish);
        // last
        $this->assertEquals("2016-07-01 12:00:00", $uPosts[2]->datePublish);
    }

    /**
     * @covers PostDAOService::fetchUserPosts
     * @depends testCanGetAUsersPost
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must inform a user to list posts !
     */
    public function testCanotGetPostsFromNobody() {
        $postDAOService = $this->getPostDAOService();
        $postDAOService->fetchUserPosts(null);
    }

    /**
     * @covers PostDAOService::fetchUserFeed
     * @depends testCanCreateAndRetrieve
     */
    public function testCanGetUsersFeed() {
        $userDAOService = $this->getUserDAOService();
        $postDAOService = $this->getPostDAOService();

        $users = $this->createGenericUsers(3);
        $userDAOService->createFriendship($users[0], $users[1]);
        $userDAOService->createFriendship($users[2], $users[1]);

        $posts = [];

        // user 1
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 13:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 18:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-02 12:00:00");

        // user 2
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 12:00:00");

        // user 3
        $posts[] = $this->newPost("a post", $users[2], "2016-07-01 01:00:00");
        $posts[] = $this->newPost("a post", $users[2], "2016-07-03 18:00:00");

        foreach($posts as $post)
            $postDAOService->save($post);

        $feed = $postDAOService->fetchUserFeed($users[0]);
        $this->assertCount(7, $feed);

        $this->assertEquals("2016-07-03 13:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-03 12:00:00", $feed[1]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-02 12:00:00", $feed[2]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-01 18:00:00", $feed[3]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 11:00:00", $feed[6]->datePublish->format("Y-m-d H:i:s"));

        $feed = $postDAOService->fetchUserFeed($users[1]);
        $this->assertCount(9, $feed);

        $this->assertEquals("2016-07-03 18:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-03 13:00:00", $feed[1]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-03 12:00:00", $feed[2]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-02 12:00:00", $feed[3]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 01:00:00", $feed[8]->datePublish->format("Y-m-d H:i:s"));

        $feed = $postDAOService->fetchUserFeed($users[2]);
        $this->assertCount(4, $feed);

        $this->assertEquals("2016-07-03 18:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-03 13:00:00", $feed[1]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-03 12:00:00", $feed[2]->datePublish->format("Y-m-d H:i:s"));
        $this->assertEquals("2016-07-01 01:00:00", $feed[3]->datePublish->format("Y-m-d H:i:s"));

        $feed = $postDAOService->fetchUserFeed($users[0], 5, 1);
        $this->assertCount(5, $feed);

        // first
        $this->assertEquals("2016-07-03 12:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 18:00:00", $feed[4]->datePublish->format("Y-m-d H:i:s"));
    }

    /**
     * @covers PostDAOService::fetchUserFeed
     * @depends testCanGetUsersFeed
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must inform a user to list posts !
     */
    public function testCanotGetFeedFromNobody() {
        $postDAOService = $this->getPostDAOService();
        $postDAOService->fetchUserFeed(null);
    }

    /**
     * @covers PostDAOService::getUserFeedAdapterPaginator
     * @depends testCanCreateAndRetrieve
     */
    public function testGetPaginatedFeed() {
        $userDAOService = $this->getUserDAOService();
        $postDAOService = $this->getPostDAOService();

        $users = $this->createGenericUsers(3);
        $userDAOService->createFriendship($users[0], $users[1]);
        $userDAOService->createFriendship($users[2], $users[1]);

        $posts = [];

        // user 1
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 13:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-01 18:00:00");
        $posts[] = $this->newPost("a post", $users[0], "2016-07-02 12:00:00");

        // user 2
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $users[1], "2016-07-03 12:00:00");

        // user 3
        $posts[] = $this->newPost("a post", $users[2], "2016-07-01 01:00:00");
        $posts[] = $this->newPost("a post", $users[2], "2016-07-03 18:00:00");

        foreach($posts as $post)
            $postDAOService->save($post);

        $paginator = $postDAOService->getUserFeedAdapterPaginator($users[0]);
        $this->assertNotNull($paginator);
        $this->assertTrue($paginator instanceof AdapterInterface, "It is not a AdapterInterface");

        $this->assertEquals(7, $paginator->count());
        $feed = $paginator->getItems(2, 5);
        $this->assertCout(4, $feed);

        // first
        $this->assertEquals("2016-07-02 12:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 11:00:00", $feed[3]->datePublish->format("Y-m-d H:i:s"));

        $paginator = $postDAOService->getUserFeedAdapterPaginator($users[2]);
        $this->assertEquals(4, $paginator->count());
        $feed = $paginator->getItems(2, 5);
        $this->assertCout(2, $feed);

        // first
        $this->assertEquals("2016-07-03 12:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 01:00:00", $feed[3]->datePublish->format("Y-m-d H:i:s"));
    }

    /**
     * @covers PostDAOService::getUserFeedAdapterPaginator
     * @depends testGetPaginatedFeed
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must inform a user to list posts !
     */
    public function testCanotGetFeedPaginatorFromNobody() {
        $postDAOService = $this->getPostDAOService();
        $postDAOService->getUserFeedAdapterPaginator(null);
    }

    /**
     * @covers PostDAOService::save
     * @depends testCanCreateAndRetrieve
     */
    public function testCanGetAUsersPostPaginator() {
        $userDAOService = $this->getUserDAOService();
        $postDAOService = $this->getPostDAOService();

        $user = $this->createGenericUsers(1)[0];

        $posts = [];
        $posts[] = $this->newPost("a post", $user, "2016-07-01 12:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-01 11:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-03 13:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-01 18:00:00");
        $posts[] = $this->newPost("a post", $user, "2016-07-02 12:00:00");

        foreach($posts as $post)
            $postDAOService->save($post);
        
        $paginator = $postDAOService->getUserPostsAdapterPaginator($user);
        $this->assertCount(5, $paginator->count());
        $feed = $paginator->getItems(2, 5);
        $this->assertCout(3, $feed);

        // first
        $this->assertEquals("2016-07-01 18:00:00", $feed[0]->datePublish->format("Y-m-d H:i:s"));
        // last
        $this->assertEquals("2016-07-01 11:00:00", $feed[2]->datePublish->format("Y-m-d H:i:s"));
    }

    /**
     * @covers PostDAOService::getUserPostsAdapterPaginator
     * @depends testCanGetAUsersPostPaginator
     * @expectedException \Core\Model\DAO\Exception\DAOException
     * @expectedExceptionMessage Must inform a user to list posts !
     */
    public function testCanotGetUserPostsPaginatorFromNobody() {
        $postDAOService = $this->getPostDAOService();
        $postDAOService->getUserPostsAdapterPaginator(null);
    }

}