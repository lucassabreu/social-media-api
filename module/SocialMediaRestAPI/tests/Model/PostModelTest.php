<?php

namespace SocialMediaRestAPITest\Model;

include_once __DIR__ . '/../Traits/UserTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPITest\Traits\UserTestTrait;
use DateTime;

class PostModelTest extends TestCase {

    use UserTestTrait;

    public function setup () {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        parent::setUp();
    }

    /**
     * @covers Post:getInputFilter
     * @covers Post::__set
     * @covers Post::__get
     * @covers Post::valid
     */
    public function testPostInputFilter () {
        $post = new Post();

        $if = $post->getInputFilter();
        $this->assertNotNull($if);

        $this->assertTrue($if->has('id'));
        $this->assertTrue($if->has('user'));
        $this->assertTrue($if->has('datePublish'));
        $this->assertTrue($if->has('text'));

        $post->id = '01.0';
        $this->assertEquals($post->id, 1);
        $post->text = '   <b>something funny</b>  ';
        $this->assertEquals($post->text, "something funny");
    }

    /**
     * @covers Post::setData
     * @covers Post::exchangeArray
     * @covers Post::getData
     * @covers Post::getArrayCopy
     * @covers Post::toArray
     * @covers Post::valid
     */
    public function testGetAndSetDataIntoPost () {
        $values = [
            "id" => '01.0',
            "user" => $this->newUser(),
            "datePublish" => new DateTime(),
            'text' => '   <b>something funny</b>  ',
        ];

        $post = new Post();
        $post->setData($values);
        $data = $post->getData();

        $post = new Post();
        $post->exchangeArray($values);
        $data = $post->toArray();
        $this->validatePostData($data);

        $data = $post->getArrayCopy();
        $this->validatePostData($data);

        $this->validatePostData($data);

        return $post;
    }

    private function validatePostData ($data) {
        $this->assertEquals($data['id'], 1);
        $this->assertEquals($data['text'], "something funny");
        $this->assertEquals(get_class($data['user']), User::class);
        $this->assertEquals(get_class($data['datePublish']), DateTime::class);
    }

    /**
     * @depends testGetAndSetDataIntoPost
     * @covers Post::valid
     * @covers Post::validate
     */
    public function testCanValidatePost($post) {
        $this->assertTrue($post->validate());
    }

    /**
     * @covers Post::valid
     * @covers Post::validate
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testIfNothingWasInsertedMustThrowError() {
        $post = new Post();
        $this->assertFalse($post->validate());
    }

    /**
     * @covers Post::valid
     * @covers Post::validate
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testCantPostNothing() {
        $post = new Post();
        $post->text = "";
    }

    /**
     * @covers Post::valid
     * @covers Post::validate
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testCantSetInvalidDate() {
        $post = new Post();
        $post->datePublish = "ipsum";        
    }

    /**
     * @covers Post::valid
     * @covers Post::validate
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testPublishDateMustInformmed() {
        $post = new Post();
        $post->datePublish = null;
    }

}