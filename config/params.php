<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;

Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}data");
Yii::setAlias('@credencials',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}credentials{$s}monitor-app-96f0293a0153.json");

return [
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
];

