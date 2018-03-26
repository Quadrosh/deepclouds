<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controllerMap' => [
        'gearman' => [
            'class' => 'shakura\yii2\gearman\GearmanController',
            'gearmanComponent' => 'gearman'
        ],
    ],
    'log' => [
        'traceLevel' => YII_DEBUG ? 3 : 0,
        'targets' => [
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['error', 'warning'],
            ],
            [
                'class' => 'yii\log\FileTarget',
                'levels' => ['info'],
                'categories' => ['b2bBot'],
                'logFile' => '@runtime/logs/b2bBot.log',
                'logVars' => [],   // $_GET, $_POST, $_FILES, $_COOKIE, $_SESSION, $_SERVER
                'maxFileSize' => 1024 * 2,
                'maxLogFiles' => 20,
            ],
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
