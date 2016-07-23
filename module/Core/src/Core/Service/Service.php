<?php

namespace Core\Service;

use Core\Service\Service;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Session\Container;
use Zend\Session\SessionManager;

/**
 * Service base class
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Service {

    /**
     * @var SessionManager
     */
    private $sessionManager;

    /**
     * Returns information of session user, or null if has no user logged
     * @param string (optional) $attribute Attribute of session wanted
     * @return mixed|null
     */
    public function getCurrentUser() {

        $authService = new AuthenticationService();
        /* @var $authService AuthenticationService */

        $user = $authService->getIdentity();

        return $user;
    }

    /**
     * Retrieves a <code>Container</code> associated with the session.
     * @param string $name
     * @return Container
     */
    protected function getSessionContainer($name = "Default") {
        // $this->getService('Zend\Session\SessionManager');
        return new Container($name);
    }

    /**
     * Sets the <code>SessionManager</code>
     * @param SessionManager
     */
    public function setSessionManager(SessionManager $sm) 
    {
        $this->sessionManager = $sm;
    }

    /**
     * Retrieves the <code>SessionManager</code> associated with the session
     */
    protected function getSessionManager() {
        return $this->sessionManager;
    }
}

?>
