<?php

namespace SocialMediaRestAPITest\Traits;

use SocialMediaRestAPI\Model\Entity\Post;
use SocialMediaRestAPI\Service\PostDAOService;

/**
 * Some methods to help test post related things
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 *
 * @see \SocialMediaRestAPI\Model\Entity\Post
 * @see \SocialMediaRestAPI\Service\PostDAOService
 */
trait PostTestTrait {

    /**
     * Retrieve the <code>PostDAOService</code>
     * @return PostDAOService
     */
    private function getPostDAOService() {
        return $this->getServiceManager()->get('SocialMediaRestAPI\Service\PostDAOService');
    }

}