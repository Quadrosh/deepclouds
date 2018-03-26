<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controllerMap' => [
        'gearman' => [
            'class' => 'shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearman'
        ],
    ],
    'components' => [
        'gearman' => [
            'class' => 'shakura\yii2\gearman\GearmanComponent',
            'servers' => [
                ['host' => '127.0.0.1', 'port' => 4730],
            ],
            'user' => 'www-data',
            'jobs' => [
                'simpleJob' => [
                    'class' => 'common\jobs\SimpleJob'
                ],
                'syncCalendar' => [
                    'class' => 'common\jobs\SyncCalendar'
                ],
            ]
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],

    ],
];
