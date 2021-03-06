<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'monitor-beta-prochile',
    'name' => 'ProChile 🇨🇱',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'timeZone' => 'America/Santiago',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'modules' => [
        'monitor' => [
            'class' => 'app\modules\monitor\Module',
        ],
        'topic' => [
            'class' => 'app\modules\topic\Module',
        ],
        'user' => [
            'class' => 'app\modules\user\Module',
        ],
        'insights' => [
            'class' => 'app\modules\insights\Module',
        ],
        'wordlists' => [
            'class' => 'app\modules\wordlists\Module',
        ],
        // kartik
        'gridview' => [
            'class' => '\kartik\grid\Module',
            'bsVersion' => 3,
            //'downloadAction' => 'gridview/export/download',
        ]
    ],
    'components' => [
        'assetManager' => [
            'linkAssets' => true,
            'appendTimestamp' => true,
        ],
        'formatter' => [
           'dateFormat' => 'dd/mm/yyyy',
       ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'cwAyJzhAYoJKZywPh0oEVaVAk_akHdXR',
        ],
        'cache' => [
            'class' => 'yii\caching\DbCache',
            'db' => $db,
            'cacheTable' => 'cache',
        ],
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                 // rule for wordlists
                 'wordlists' => 'wordlists/default/index',
                 'wordlists/create' => 'wordlists/default/create',
                 'wordlists/view' => 'wordlists/default/view',
                 'wordlists/update' => 'wordlists/default/update',
                 // end rules module wordlists
            ],
        ],
        
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];

}

return $config;
