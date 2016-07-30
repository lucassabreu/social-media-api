<?php

namespace Core\Authentification\Http;

use RuntimeException;
use Core\Service\Util\ParameterInstanciatorTrait;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\Adapter\Http as HttpAdapter;

class AuthentificationAdapterFactory implements FactoryInterface {

    use ParameterInstanciatorTrait;

    public function createService (ServiceLocatorInterface $serviceLocator) {
        $config = $serviceLocator->get('Config');

        if (!isset($config['http_auth']) || !isset($config['http_auth']['adapter']))
            throw new RuntimeException(sprinf('To use ' . __CLASS__ . ' you must inform the config at $config["http_auth"]["adapter"]'));

        $authConfig = $config['http_auth'];
        $adapter = new HttpAdapter($authConfig['adapter']['options']);
        $adapter->setRequest($serviceLocator->get('Request'));
        $adapter->setResponse($serviceLocator->get('Response'));

        if (isset($authConfig['resolvers'])) {
            $values = $this->returnInstanceOf($authConfig['resolvers']['basic_resolver'], 
                        $serviceLocator);
            if (isset($authConfig['resolvers']['basic_resolver']))
                $adapter->setBasicResolver(
                    $this->returnInstanceOf($authConfig['resolvers']['basic_resolver'], 
                        $serviceLocator));
            
            if (isset($authConfig['resolvers']['digest_resolver']))
                $adapter->setDigestResolver(
                    $this->returnInstanceOf($authConfig['resolvers']['digest_resolver'], 
                        $serviceLocator));
        }

        return $adapter;
    }
    

}