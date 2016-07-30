<?php

namespace SocialMediaRestAPI;

use Zend\Authentication\AuthenticationService;
use SocialMediaRestAPI\Authentication\Resolver\UserResolver;

return [
    'http_auth' => [
        'adapter' => [
            'options' => [
                'accept_schemes' => 'basic',
                'realm' => '/api',
            ],
        ],
        'resolvers' => [
            'basic_resolver' => function ($sm) {
                $userDAOService = $sm->get('SocialMediaRestAPI\Service\UserDAOService');
                return new UserResolver($userDAOService);
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
                'service' => 'SocialMediaRestAPI\Service\PostDAOService',
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
                $auth = $sl->get('Zend\Authentication\AuthenticationService');
                return new Controller\UserRestController($dao, $auth);
            },
            'SocialMediaRestAPI\Controller\FriendRest' => function($sm) {
                $sl = $sm->getServiceLocator();
                $dao = $sl->get('SocialMediaRestAPI\Service\UserDAOService');
                $auth = $sl->get('Zend\Authentication\AuthenticationService');
                return new Controller\FriendRestController($dao, $auth);
            },
            'SocialMediaRestAPI\Controller\PostRest' => function($sm) {
                $sl = $sm->getServiceLocator();
                $dao = $sl->get('SocialMediaRestAPI\Service\PostDAOService');
                $auth = $sl->get('Zend\Authentication\AuthenticationService');
                return new Controller\PostRestController($dao, $auth);
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
            'user-self-rest' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/api/users/self',
                    'defaults' => [
                        'action' => 'self',
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
            'posts-feed-rest' => [
                'type'    => 'literal',
                'options' => [
                    'route'    => '/api/feed',
                    'defaults' => [
                        'action' => 'feed',
                        'controller' => 'SocialMediaRestAPI\Controller\PostRest',
                    ],
                ],
            ],
            'posts-rest' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/posts[/[:id]]',
                    'constraints' => [
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'SocialMediaRestAPI\Controller\PostRest',
                    ],
                ],
            ],
            'posts-user-rest' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/api/posts/user/[:userId]',
                    'constraints' => [
                        'userId'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'action' => 'byUser',
                        'controller' => 'SocialMediaRestAPI\Controller\PostRest',
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