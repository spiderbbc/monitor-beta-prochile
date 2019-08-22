<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;

Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}data");

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
];
