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
            'SocialMediaRestAPI\Controller\UserRest' => Controller\UserRestController::class,
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