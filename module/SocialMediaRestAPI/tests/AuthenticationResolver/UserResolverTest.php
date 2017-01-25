<?php

namespace SocialMediaRestAPITest\AuthentificationResolver;

include_once __DIR__ . '/../Traits/ModelHelpTestTrait.php';

use Core\Test\TestCase;
use SocialMediaRestAPI\Authentication\Resolver\UserResolver;
use SocialMediaRestAPI\Model\Entity\User;
use SocialMediaRestAPITest\Traits\ModelHelpTestTrait;

class UserResolverTest extends TestCase {

    use ModelHelpTestTrait;

    public function setUp() {
        $this->setApplicationConfig(\Bootstrap::getTestConfig());
        $this->setUpDatabase = true;
        parent::setUp();
    }

    public function getUserResolver () {
        return new UserResolver ($this->getUserDAOService());
    }

    public function testCanAuthenticate () {
        $userDAOService = $this->getUserDAOService();
        $resolver = $this->getUserResolver();

        $user = $this->newUser('lucas.s.abreu@gmail.com',
                               'Lucas dos Santos Abreu',
                               '123456');
        $userDAOService->save($user);

        $return = $resolver->resolve('lucas.s.abreu@gmail.com', 'rest-api', '123456');

        $this->assertArrayHasKey('username', $return);
        $this->assertArrayHasKey('realm', $return);
        $this->assertArrayHasKey('user', $return);

        $this->assertEquals($return['username'], 'lucas.s.abreu@gmail.com');
        $this->assertTrue($return['user'] instanceof User);
        $this->assertEquals($return['user']->id, 1);

        $return = $resolver->resolve('lucas.s.abreu@gmail.com', 'rest-api', 'errado');

        $this->assertFalse($return);

        $return = $resolver->resolve('joaozinho@gmail.com', 'rest-api', '123456');
        
        $this->assertFalse($return);
    }

    /**
     * @expectedException \Zend\Authentication\Adapter\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Username is required
     */
    public function testMustBeInformmedAUsername () {
        $resolver = $this->getUserResolver();
        $return = $resolver->resolve('', 'rest-api', '123456');
    }

    /**
     * @expectedException \Zend\Authentication\Adapter\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Username must consist only of printable characters, excluding the colon
     */
    public function testUsernameMustBePrintable () {
        $resolver = $this->getUserResolver();
        $return = $resolver->resolve('lucas' . chr(2), 'rest-api', '123456');
    }

    /**
     * @expectedException \Zend\Authentication\Adapter\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Username must consist only of printable characters, excluding the colon
     */
    public function testUsernameMustBePrintableExceptionColon () {
        $resolver = $this->getUserResolver();
        $return = $resolver->resolve('lucas.s.abreu:gmail.com', 'rest-api', '123456');
    }

    /**
     * @expectedException \Zend\Authentication\Adapter\Http\Exception\InvalidArgumentException
     * @expectedExceptionMessage Password is required
     */
    public function testMustBeInformmedAPassword () {
        $resolver = $this->getUserResolver();
        $return = $resolver->resolve('lucas.s.abreu@gmail.com', 'rest-api', '');
    }
}
