<?php

namespace SocialMediaRestAPI;

use Zend\Authentication\AuthenticationService;

return [
    'http_auth' => [
        'adapter' => [
            'options' => [
                'accept_schemes' => ['basic'],
                'realm' => 'social-media-api',
            ],
        ],
        'resolvers' => [
            'basic_resolver' => function ($sm) {
                $userDAOService = $sm->get('SocialMediaRestAPI\Service\UserDAOService');
                return Authentication\Resolver\UserResolver($userDAOService);
            }
        ]
    ],
    'service_manager' => [
        'factories' => [
            'Zend\Authentication\Adapter\Http' => 
                'Core\Authentification\Http\AuthentificationAdapterFactory',
            'Zend\Authentication\AuthenticationService' => function ($sm) {
                $adapter = $sm->get('Zend\Authentication\Adapter\Http');
                return new AuthenticationService(null, $adapter);
            },
        ],
        'dao_services' => [
            'SocialMediaRestAPI\Service\UserDAOService' => [
                'service' => 'SocialMediaRestAPI\Service\UserDAOService',
                'model' => 'SocialMediaRestAPI\Model\Doctrine\UserDAODoctrine',
            ],
            'SocialMediaRestAPI\Service\PostDAOService' => [
                'service' => function($sm) {
                    $dao = $sm->get('SocialMediaRestAPI\Service\UserDAOService');
                    return new Service\PostDAOService($dao);
                },
                'model' => 'SocialMediaRestAPI\Model\Doctrine\PostDAODoctrine',
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
        ],
        'factories' => [
            'SocialMediaRestAPI\Controller\UserRest' => function($sm) {
                $sl = $sm->getServiceLocator();
                $dao = $sl->get('SocialMediaRestAPI\Service\UserDAOService');
                return new Controller\UserRestController($dao);
            },
            'SocialMediaRestAPI\Controller\FriendRest' => function($sm) {
                $sl = $sm->getServiceLocator();
                $dao = $sl->get('SocialMediaRestAPI\Service\UserDAOService');
                return new Controller\FriendRestController($dao);
            },
        ],
    ],
    'router' => [
        'routes' => [
            'users-rest' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/users[/[:id]]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'SocialMediaRestAPI\Controller\UserRest',
                    ],
                ],
            ],
            'user-password-rest' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/api/users/:id/change-password',
                    'constraints' => array(
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'SocialMediaRestAPI\Controller\UserRest',
                        'action' => 'changePassword',
                    ),
                ),
            ),
            'user-friends-rest' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/users/:userId/friends[/[:id]]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                        'user'   => '[0-9]+'
                    ],
                    'defaults' => [
                        'controller' => 'SocialMediaRestAPI\Controller\FriendRest',
                    ],
                ],
            ],
        ],
    ],
    // Doctrine config
    'doctrine' => [
        'driver' => [
            __NAMESPACE__ . '_driver' => [
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => [__DIR__ . '/../src/' . __NAMESPACE__ . '/Model/Entity']
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Model\Entity' => __NAMESPACE__ . '_driver'
                ]
            ]
        ]
    ],
    'view_manager' => [ //Add this config
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];