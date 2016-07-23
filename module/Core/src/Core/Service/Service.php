<?php

namespace Core\Service;

use Core\Service\Service;
use Zend\Authentication\AuthenticationService;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\ServiceManagerAwareInterface;
use Zend\Session\Container;

/**
 * Service base class
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
class Service implements ServiceManagerAwareInterface {

    /**
     * @var ServiceManager
     */
    private $serviceManager = null;

    public function getServiceManager() {
        return $this->serviceManager;
    }

    public function setServiceManager(ServiceManager $serviceManager) {
        $this->serviceManager = $serviceManager;
        return $this;
    }

    /**
     * Retrieves a service by the name.
     * @param string $name Name of requested service.
     * @return Service | mixed
     */
    public function getService($name) {
        return $this->getServiceManager()->get($name);
    }

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
        $this->getService('Zend\Session\SessionManager');
        return new Container($name);
    }

}

?>
