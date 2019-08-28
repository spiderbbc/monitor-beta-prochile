<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;



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
		
		//count product alert for get products_count
		$this->products_count = $this->countAllTerms($alerts);

		if($this->products_count){
			for($a = 0; $a < sizeOf($alerts); $a++){
				for($c = 0; $c < sizeOf($alerts[$a]['config']['configSources']); $c++){
					$name = $alerts[$a]['config']['configSources'][$c];
					
					if(ArrayHelper::keyExists($name,$this->className, false)){
						
						$className = $this->className[$name];
						$modelApi = $this->{$className}($alerts[$a]);
					}
				}
			}
		}
	}

	public function twitterApi($alert = []){
		$tweets = new \app\models\api\TwitterApi($this->products_count);
		$products_params = $tweets->prepare($alert);
		if($products_params){
			$data = $tweets->call($products_params);
		}

		//echo "twitterApi". "\n";
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


	
}

?>