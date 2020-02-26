<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=monitor-prochile',
    'username' => 'phpmyadmin',
    'password' => 'deathnote',
    //'charset' => 'utf8',
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];


/*return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=lgmontan_monitor',
    'username' => 'lgmontan_root',
    'password' => 'z@xQUABP}A0[',
    //'charset' => 'utf8',
    'charset' => 'utf8mb4',

    // Schema cache options (for production environment)
    'enableSchemaCache' => true,
    'schemaCacheDuration' => 60,
    'schemaCache' => 'cache',
];*/

