<?php
 
$db     = require(__DIR__ . '/../../config/db.php');
$params = require(__DIR__ . '/params.php');
 
$config = [
    'id' => 'basic',
    'name' => 'TimeTracker',
    // Need to get one level up:
    'basePath' => dirname(__DIR__).'/..',
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            //'cookieValidationKey' => 'cwAyJzhAYoJKZywPh0oEVaVAk_akHdXR',
            // Enable JSON Input:
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                     // Create API log in the standard log dir
                     // But in file 'api.log':
                    'logFile' => '@app/runtime/logs/api.log',
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => false,
            'showScriptName' => false,
            'rules' => [
               // ['class' => 'yii\rest\UrlRule', 'controller' => ['v1/project','v1/time']],
                ['class' => 'yii\rest\UrlRule', 'controller' => 'v1/mentions'],
            ],
        ], 
        'db' => $db,
        'user' => [
            'identityClass' => 'app\models\Users',
            'enableAutoLogin' => false,
        ], 
    ],
    'modules' => [
        'v1' => [
            'basePath' => '@app/api/modules/v1',
            'class' => 'app\api\modules\v1\Module',
        ],
    ],
    'params' => $params,
];
 
return $config;