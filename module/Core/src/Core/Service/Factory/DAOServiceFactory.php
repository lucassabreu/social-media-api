<?php

namespace Core\Service\Factory;

use Closure;
use Core\Model\DAO\DAOInterface;
use Core\Service\DAOServiceInterface;
use Exception;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Core\Service\Util\ParameterInstanciatorTrait;

/**
 * Factory of DAO Service classes.
 *
 * @author Lucas dos Santos Abreu <lucas.s.abreu@gmail.com>
 */
final class DAOServiceFactory implements AbstractFactoryInterface {

    use ParameterInstanciatorTrait;

    protected $services = array();

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    protected function getConfig() {
        $gConfig = $this->serviceLocator->get('Configuration');

        if (isset($gConfig['service_manager']) && isset($gConfig['service_manager']['dao_services'])) {
            return $gConfig['service_manager']['dao_services'];
        } else {
            return array();
        }
    }

    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $this->serviceLocator = $serviceLocator;
        $config = $this->getConfig();
        return isset($config[$requestedName]);
    }

    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName) {
        $this->serviceLocator = $serviceLocator;
        $config = $this->getConfig();

        if (!isset($this->services[$requestedName]) || $this->services[$requestedName] === null) {
            $sm = $this->serviceLocator->get('Zend\Session\SessionManager');
            $em = $this->serviceLocator->get('Doctrine\ORM\EntityManager');

            /**
             * @var DAOServiceInterface
             */
            $service = $this->returnInstanceOf($config[$requestedName]['service'], $this->serviceLocator);
            $model = $this->returnInstanceOf($config[$requestedName]['model'], $this->serviceLocator);

            $model->setEntityManager($em);

            $service->setSessionManager($sm);
            $service->setDAOInterface($model);
            $service->setServiceLocator($serviceLocator);

            $this->services[$requestedName] = $service;
        }

        return $this->services[$requestedName];
    }

}

?>
