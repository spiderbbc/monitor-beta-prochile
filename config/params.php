<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;

Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}data");
Yii::setAlias('@img',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}web{$s}img");
Yii::setAlias('@pdf',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}web{$s}pdf");
Yii::setAlias('@credencials',dirname(dirname(__DIR__)). "{$s}monitor-beta{$s}credentials{$s}monitor-app-96f0293a0153.json");

return [
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
	'facebook'    => [ 
		'time_min_sleep'  => 5,  
		'business_id' => '101330848134001',
		'app_id'      => '227526951746847',
		'name_app'    => 'pro_chile_monitor',
		'app_secret'  => '6fad2007ef6412fcf59a5581ac6c764b'

	],
];

