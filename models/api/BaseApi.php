<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;


use app\models\file\JsonFile;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * BaseApi is the model behind the calls to models for API.
 *
 */
class BaseApi extends Model {

	public $alerts;
	public $products_count = 0;
	public $data;

	public $className = [
		'Twitter' => 'twitterApi',
		'Live Chat' => 'liveChat',
		'Live Chat Conversations' => 'liveChatConversations',
		'Web page' => 'webpage',
	];

	public function callResourcesApi($alerts = []){
		
		Console::stdout("Running: ".__METHOD__."\n", Console::BOLD);
		if(!empty($alerts)){
			
			$resources = [];

			for($a = 0; $a < sizeOf($alerts); $a++){
				for($c = 0; $c < sizeOf($alerts[$a]['config']['configSources']); $c++){
					$name = $alerts[$a]['config']['configSources'][$c];
					
					if(ArrayHelper::keyExists($name,$this->className, false)){
						$className = $this->className[$name];
						$resources[$className][] = $alerts[$a]; 
					}
				}
			}

			foreach($resources as $method => $alerts){
				$this->{$method}($alerts);
			}
		} // if alert

		
	}

	public function twitterApi($alerts = []){
		
		Console::stdout("calling twitter api class\n", Console::BOLD);
		$products_count = $this->countAllTerms($alerts);
		$tweets = new \app\models\api\TwitterApi($products_count);

		foreach ($alerts as $alert) {
			$products_params = $tweets->prepare($alert);
				if($products_params){
					$data = $tweets->call($products_params);
					
					// path to folder flat archives
					$folderpath = [
						'resource' => 'twitter',
						'documentId' => $alert['id'],
						'fileName' => time(),
					];
					$this->saveJsonFile($folderpath,$data);
			}
			
		}
		echo "twitterApi". "\n";
	}

	public function liveChat($alerts = []){
		echo "liveChat". "\n";
	}

	public function liveChatConversations($alerts = []){
		echo "liveChatConversations". "\n";
	}

	public function webpage($alerts = []){
		echo "webpage". "\n";
	}

	public function countAllTerms($alerts = []){
		$count = 0;
		for($a = 0; $a < sizeOf($alerts); $a++){
			if(ArrayHelper::keyExists('products', $alerts[$a], false)){
				$count += count($alerts[$a]['products']);
			}
		}
		return $count;
	}



	public function saveJsonFile($folderpath = [],$data){

		if(!empty($data)){
			// call jsonfile
			$jsonfile = new JsonFile($folderpath);
			$jsonfile->load($data);
			$jsonfile->save();
		}

	}
	
}

?>