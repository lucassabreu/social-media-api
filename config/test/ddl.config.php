<?php

return [
    'user' => [
        'create' => [
            "CREATE TABLE `users` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `username` varchar(100) NOT NULL,
                `password` char(32) NOT NULL,
                `name` varchar(150) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_user_name` (`username`),
                KEY `idx_user_name_login` (`username`,`password`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;",
        ],
        'drop' => 'DROP TABLE users;',
    ],
    'posts' => [
        'create' => [ 
            "CREATE TABLE `posts` (
                `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                `userId` int(11) unsigned NOT NULL,
                `text` varchar(250) NOT NULL,
                `datePublish` datetime(6) NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `post_id_UNIQUE` (`id`),
                KEY `fk_user_idx` (`userId`),
                KEY `idx_date_publish` (`datePublish`,`userId`),
                CONSTRAINT `fk_post_user` FOREIGN KEY (`userId`) 
                    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;",
        ],
        'drop' => 'DROP TABLE posts;',
    ],
    'friendship' => [
        'create' => [ 
            "CREATE TABLE `friendships` (
                `userId` int(11) unsigned NOT NULL,
                `userFriendId` int(11) unsigned NOT NULL,
                PRIMARY KEY (`userId`,`userFriendId`),
                KEY `fk_user_friend_idx` (`userFriendId`),
                CONSTRAINT `fk_user` FOREIGN KEY (`userId`) 
                    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                CONSTRAINT `fk_user_friend` FOREIGN KEY (`userFriendId`) 
                    REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;"
        ],
        'drop' => 'DROP TABLE friendships;',
    ],
];