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
	'newsApi'     => [
		'time_hours_sleep' => 8,
		'apiKey' => 'ca5709f738c146a0b5f86fad213c9316',
		'targets' => [
			/*'Forbes' => 'forbes.com',
			'Techcrunch' => 'techcrunch.com',
			'New York Times' => 'nytimes.com',
			'Houston Chronicle' => 'chron.com',
			'The Economist' => 'economist.com',
			'Miami Herald' => 'miamiherald.com',
			'Los Angeles Times' => 'latimes.com',
			'The Wall Street Journal' => 'wsj.com'*/
			/*'lun' => 'lun.com',
			'lgblog' => 'lgblog.cl',
			'eldinamo' => 'eldinamo.cl',
			'soychile' => 'soychile.cl',
			'transmedia' => 'transmedia.cl',
			'fayerwayer' => 'fayerwayer.com',
			'cooperativa' => 'cooperativa.cl',*/
		],
	],
	'facebook'    => [ 
		'time_min_sleep'  => 5,  
		'business_id' => '169441517247',
		'app_id'      => '446684435912359',
		'name_app'    => 'monitor-facebook',
		'app_secret'  => '541f2431cc1ad60c9d5bb4836eed1356'

	],
];

