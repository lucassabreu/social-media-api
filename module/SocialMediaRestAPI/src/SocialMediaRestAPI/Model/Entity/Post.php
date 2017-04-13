<?php

namespace SocialMediaRestAPI\Model\Entity;

use Core\Model\Entity\Entity;
use Core\Model\Entity\EntityTrait;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Zend\InputFilter\Factory;
use Zend\Validator\NotEmpty;
use Core\Filter\DateTime as DateTimeFilter;

/**
 * Class that represents a persistent post entity
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @ORM\Entity
 * @ORM\Table (name="posts")
 *
 * @property int $id
 * @property User $user
 * @property DateTime $datePublish
 * @property string $text
 */
class Post extends Entity
{
    use EntityTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(name="userId", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $datePublish;

    /**
     * @ORM\Column(type="string")
     */
    private $text;

    public function getInputFilter()
    {
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
                'user' => [
                    'name' => 'user',
                    'required' => true,
                    'validators' => [
                        [
                            'name' => 'NotEmpty',
                            'options' => [NotEmpty::NULL]
                        ]
                    ],
                ],
                'datePublish' => [
                    'name' => 'datePublish',
                    'required' => true,
                    'filters' => [
                        ['name' => DateTimeFilter::class]
                    ],
                    'validators' => [
                        [
                            'name' => 'Date',
                            'options' => [
                                'format' => "Y-m-d H:i:s"
                            ]
                        ],
                        [
                            'name' => 'NotEmpty',
                            'options' => [NotEmpty::NULL]
                        ]
                    ],
                ],
                'text' => [
                    'name' => 'text',
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
                                'max' => 250,
                            ]
                        ],
                        [
                            'name' => 'NotEmpty',
                            'options' => [NotEmpty::NULL | NotEmpty::STRING ]
                        ]
                    ],
                ]
            ]);
        }

        return $this->inputFilter;
    }
}
