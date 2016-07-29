<?php

namespace SocialMediaRestAPITest\Traits;

trait HttpAuthorizationBasicTrait {

    /**
     * Sets the Authorization header with a hash based on the $username and $password
     * @param string $username
     * @param string $password
     * @return void 
     */
    private function setAuthorizationHeader ($username, $password) {
        $hash = base64_encode("$username:$password");
        $headers = $this->getRequest()->getHeaders();
        $headers->addHeaderLine('Authorization', "Basic $hash");
    }

}