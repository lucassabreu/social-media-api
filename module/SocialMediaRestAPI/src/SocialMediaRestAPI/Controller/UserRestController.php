<?php

namespace SocialMediaRestAPI\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

class UserRestController extends AbstractRestfulController
{

    public function getList() {
        return new JsonModel();
    }

}

