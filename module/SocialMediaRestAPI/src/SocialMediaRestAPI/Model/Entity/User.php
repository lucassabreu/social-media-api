<?php

namespace SocialMediaRestAPI\Model\Entity;

use Doctrine\ORM\Mapping as ORM;
use Core\Model\Entity\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Zend\InputFilter\Factory;

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


}