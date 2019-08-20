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
	public $limit;
	public $data;

	public $className = [
		'Twitter' => 'twitterApi',
		'Live Chat' => 'liveChat',
		'Live Chat Conversations' => 'liveChatConversations',
		'Web page' => 'webpage',
	];

	public function callResourcesApi($alerts = []){
		for($a = 0; $a < sizeOf($alerts); $a++){
			for($c = 0; $c < sizeOf($alerts[$a]['config']['configSources']); $c++){
				$name = $alerts[$a]['config']['configSources'][$c]['alertResource']['name'];
				
				if(ArrayHelper::keyExists($name,$this->className, false)){
					$className = $this->className[$name];
					$modelApi = $this->{$className}($alerts[$a]);
				}
			}
		}
	}

	public function twitterApi($alerts = []){
		$model = new \app\models\api\TwitterApi;
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


	
}

?>