<?php
return [
    'bootstrap' => [
        'queue', // Компонент регистрирует свои консольные команды
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
//    'controllerMap' => [
//        'gearman' => [
//            'class' => 'shakura\yii2\gearman\GearmanController',
//            'gearmanComponent' => 'gearman'
//        ],
//    ],

    'components' => [
        'queue' => [
            'class' => \yii\queue\gearman\Queue::class,
            'host' => 'localhost',
            'port' => 4730,
            'channel' => 'my_queue',
            'as log' => \yii\queue\LogBehavior::class,
        ],
//        'gearman' => [
//            'class' => 'shakura\yii2\gearman\GearmanComponent',
//            'servers' => [
//                ['host' => '127.0.0.1', 'port' => 4730],
//            ],
//            'user' => 'www-data',
//            'jobs' => [
//                'simpleJob' => [
//                    'class' => 'common\jobs\SimpleJob'
//                ],
//                'sendLimitedJob' => [
//                    'class' => 'common\jobs\SendLimitedJob'
//                ],
//            ]
//        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

    ],
];
