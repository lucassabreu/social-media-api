<?php

namespace SocialMediaRestAPI;

return [
    'service_manager' => [
        'dao_services' => [
            'SocialMediaRestAPI\Service\UserDAOService' => [
                'service' => 'SocialMediaRestAPI\Service\UserDAOService',
                'model' => 'SocialMediaRestAPI\Model\Doctrine\UserDAODoctrine',
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