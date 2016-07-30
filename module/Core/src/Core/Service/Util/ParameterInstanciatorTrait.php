<?php

namespace Core\Service\Util;

use Exception;
use Closure;
use Zend\ServiceManager\ServiceLocatorInterface;

trait ParameterInstanciatorTrait {

    /**
     * Retrieves a instance of param.
     * @param Closure|string $param
     * @param ServiceLocatorInterface $serviceLocator
     * @return DAOInterface
     * @throws Exception When the param not be a Closure or a valid class name.
     */
    protected function returnInstanceOf($param, ServiceLocatorInterface $serviceLocator) {
        $instance = null;

        if ($param instanceof Closure) {
            $instance = $param->__invoke($serviceLocator);
        } else {
            if (class_exists('\\' . $param)) {
                $param = ('\\' . $param);
                $instance = new $param();
            } else
                throw new Exception("A dependent object must be a anonimous function or a class name valid. Param: $param");
        }

        return $instance;
    }
}