<?php

namespace Core;

use Zend\Session\Container;
use Zend\Session\SessionManager;

return [
    'service_manager' => [
        'factories' => [
            'DbAdapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Zend\Session\SessionManager' => function ($sm) {
                $config = $sm->get('config');
                if (isset($config['session'])) {
                    $session = $config['session'];

                    $sessionConfig = null;
                    if (isset($session['config'])) {
                        $class = isset($session['config']['class']) ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                        $options = isset($session['config']['options']) ? $session['config']['options'] : [];
                        $sessionConfig = new $class();
                        $sessionConfig->setOptions($options);
                    }

                    $sessionStorage = null;
                    if (isset($session['storage'])) {
                        $class = $session['storage'];
                        $sessionStorage = new $class();
                    }

                    $sessionSaveHandler = null;
                    if (isset($session['save_handler'])) {
                        // class should be fetched from service manager since it will require constructor arguments
                        $sessionSaveHandler = $sm->get($session['save_handler']);
                    }

                    $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);

                    if (isset($session['validator'])) {
                        $chain = $sessionManager->getValidatorChain();
                        foreach ($session['validator'] as $validator) {
                            $validator = new $validator();
                            $chain->attach('session.validate', [$validator, 'isValid']);
                        }
                    }
                } else {
                    $sessionManager = new SessionManager();
                }
                Container::setDefaultManager($sessionManager);
                return $sessionManager;
            },
        ],
        'abstract_factories' => [
            'Core\Service\Factory\DAOServiceFactory'
        ],
        'invokables' => [
            'Core\Acl\Builder' => 'Core\Acl\Builder',
            'Core\Service\Util\MailUtilService' => 'Core\Service\Util\MailUtilService',
        ],
        'dao_services' => [
            // 'Admin\Service\UserDAOService' => [
            //     'service' => 'Admin\Service\UserDAOService',
            //     'model' => 'Admin\Model\Doctrine\UserDAODoctrine',
            // ],
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            'formSelect' => 'Core\View\Helper\Elements\FormSelect',
            'stripContent' => 'Core\View\Helper\StripContentHelper',
            'ztbFormButton' => 'Core\View\Helper\ZTB\ZTBFormButton',
            'ztbFormPrepare' => 'Core\View\Helper\ZTB\ZTBFormPrepare',
        ],
    ],
];
