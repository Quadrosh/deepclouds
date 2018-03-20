<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // 'useFileTransport'  true = send all mails to a file
            // 'useFileTransport'  false + configure a transport =  mailer send real emails.

            'useFileTransport' => false,
        ],
    ],
];
