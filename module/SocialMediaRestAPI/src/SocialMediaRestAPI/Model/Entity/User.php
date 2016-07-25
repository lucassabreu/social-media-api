<?php

namespace SocialMediaRestAPI\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Core\Model\Entity\Entity;
use Core\Model\Entity\EntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\Factory;
use Zend\Validator\NotEmpty;

/**
 * @ORM\Entity
 * @ORM\Table (name="users")
 * 
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $name
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com> 
 */
class User extends Entity {

	use EntityTrait;

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
    private $id;

	/**
	 * @ORM\Column(type="string")
	 */
    private $username;

	/**
	 * @ORM\Column(type="string")
	 */
    private $password;

	/**
	 * @ORM\Column(type="string")
	 */
    private $name;

	/**
	 * @ORM\ManyToMany(targetEntity="User")
	 * @ORM\JoinTable(name="friendships",
     *      joinColumns={@ORM\JoinColumn(name="userId", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="userFriendId", 
	 *									    	referencedColumnName="id", unique=true)}
	 * )
	 */
	private $friends;

	public function __construct() {
		$this->friends = new ArrayCollection();
	}

	public function getFriends() {
		return $this->friends;
	}

	public function getInputFilter () {
		if ($this->inputFilter == null) {
            $factory = new Factory();
            $this->inputFilter = $factory->createInputFilter([
				'id' => [
					'name' => 'id',
					'required' => false,
					'filter' => [
						['name' => 'Int']
					] 
				],
				'username' => [
					'name' => 'username',
					'required' => true,
					'filters' => [
						['name' => 'StripTags'],
						['name' => 'StringTrim'],
					],
					'validators' => [
						[
							'name' => 'StringLength',
							'options' => [
								'encoding' => 'UTF-8',
								'min' => 1,
								'max' => 100,
							]
						],
						[
							'name' => 'EmailAddress'
						],
						[
							'name' => 'NotEmpty',
							'options' => [NotEmpty::NULL]
						]
					],
				],
				'password' => [
					'name' => 'password',
					'required' => true,
					'filters' => [
						['name' => 'StripTags'],
						['name' => 'StringTrim'],
					],
					'validators' => [
						[
							'name' => 'NotEmpty',
							'options' => [NotEmpty::NULL]
						]
					]
				],
				'name' => [
					'name' => 'name',
					'required' => true,
					'filters' => [
						['name' => 'StripTags'],
						['name' => 'StringTrim'],
					],
					'validators' => [
						[
							'name' => 'StringLength',
							'options' => [
								'encoding' => 'UTF-8',
								'min' => 1,
								'max' => 150,
							]
						],
						[
							'name' => 'NotEmpty',
							'options' => [NotEmpty::NULL]
						]
					],
				]
			]);
		}

		return $this->inputFilter;
	}

}