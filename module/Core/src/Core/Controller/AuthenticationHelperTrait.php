<?php

namespace Core\Controller;

use Zend\Authentication\AuthenticationService;

/**
 * Helps on authentication related functions to Controllers
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
trait AuthenticationHelperTrait
{

    /**
     * Try to retrieve the identity from a <code>AuthenticationService</code>, when fails it
     * launchs a <code>AuthenticationException</code>
     * @param AuthenticationService $as
     * @return mixed|array session identity
     */
    private function getIdentity(AuthenticationService $as)
    {
        $result = $as->authenticate();
        if (!$result->isValid()) {
            throw new Exception\AuthenticationException();
        }

        return $as->getIdentity();
    }
}
