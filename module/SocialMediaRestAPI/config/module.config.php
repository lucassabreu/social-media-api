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
];