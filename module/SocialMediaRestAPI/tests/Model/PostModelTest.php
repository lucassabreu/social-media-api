<?php

namespace SocialMediaRestAPITest\Model;

include_once __DIR__ . '/../Traits/ModelHelpTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPITest\Traits\ModelHelpTestTrait;
use DateTime;

class PostModelTest extends TestCase {

    use ModelHelpTestTrait;

    public function setup () {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        parent::setUp();
    }

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
     */
    public function testCanValidatePost($post) {
        $this->assertTrue($post->validate());
    }

    /**
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testIfNothingWasInsertedMustThrowError() {
        $post = new Post();
        $this->assertFalse($post->validate());
    }

    /**
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testCantPostNothing() {
        $post = new Post();
        $post->text = "";
    }

    /**
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testCantSetInvalidDate() {
        $post = new Post();
        $post->datePublish = "ipsum";
    }

    public function testCanSetValidDateString() {
        $post = new Post();
        $post->datePublish = "2016-07-01 12:00:00";
        $this->assertTrue($post->datePublish instanceof \DateTime, "It is not a \DateTime object");
        $this->assertEquals($post->datePublish->format('Y-m-d H:i:s'), "2016-07-01 12:00:00");
    }

    /**
     * @expectedException \Core\Model\DAO\Exception\DAOException
     */
    public function testPublishDateMustInformmed() {
        $post = new Post();
        $post->datePublish = null;
    }

}
