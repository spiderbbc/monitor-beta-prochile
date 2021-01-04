<?php
// path to folder flat archives
$s = DIRECTORY_SEPARATOR;
$path = explode($s, dirname(__DIR__));
$folder = end($path);

Yii::setAlias('@data',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}data");
Yii::setAlias('@img',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}img");
Yii::setAlias('@pdf',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}web{$s}pdf");
Yii::setAlias('@credencials',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}monitor-app-96f0293a0153.json");
Yii::setAlias('@insights',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}widgets{$s}insights");
Yii::setAlias('@cacert',dirname(dirname(__DIR__)). "{$s}{$folder}{$s}credentials{$s}cacert.pem");

// set env var
$dotenv = Dotenv\Dotenv::createImmutable( dirname(dirname(__DIR__)). "{$s}{$folder}{$s}");
$dotenv->load();

return [
	'frontendUrl' => 'https://prochile.mediatrendsgroup.com/web/',
	'adminEmail'  => 'eduardo@montana-studio.com',
	'senderEmail' => 'eduardo@montana-studio.com',
	'senderName'  => 'monitor-beta',
	'scraping'    =>[
		'time_min_sleep'  => 1, 
	],
	'newsApi'     => [
		'time_hours_sleep' => 8,
		'apiKey' => 'ca5709f738c146a0b5f86fad213c9316',
		'targets' => [
			'Forbes' => 'forbes.com',
			'Techcrunch' => 'techcrunch.com',
			'New York Times' => 'nytimes.com',
			'Houston Chronicle' => 'chron.com',
			'The Economist' => 'economist.com',
			'Miami Herald' => 'miamiherald.com',
			'Los Angeles Times' => 'latimes.com',
			'The Wall Street Journal' => 'wsj.com'
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
		'business_id'     => $_SERVER['BUSSINES_ID'],
		'app_id'          => $_SERVER['APP_ID'],
		'name_app'        => $_SERVER['NAME_APP'],
		'name_account'    => $_SERVER['NAME_ACCOUNT'],
		'app_secret'      => $_SERVER['APP_SECRET']

	],
	// alias for resources
	'resourcesName' => [
		"Twitter" => "Twitter",
		"Live Chat" => "Live Chat (Tickets)",
		"Live Chat Conversations" => "Live Chat (Chats)",
		"Facebook Comments" => "Facebook Commentarios",
		"Instagram Comments" => "Instagram Commentarios",
		"Facebook Messages" => "Facebook Inbox",
		"Excel Document" => "Excel Documento",
		"Paginas Webs" => "Paginas Webs",
	],
];

