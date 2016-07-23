<?php

namespace SocialMediaRestAPI\Model\Doctrine;

use SocialMediaRestAPI\Model\DAO\UserDAOInterface;
use Core\Model\DAO\Doctrine\AbstractDoctrineDAO;

class UserDAODoctrine extends AbstractDoctrineDAO 
{
    public function __construct () {
        parent::__construct('SocialMediaRestAPI\Model\Entity\User');
    }
}
