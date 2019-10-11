<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;


use Facebook\Facebook;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FacebookApi is the model behind the login API.
 *
 */
class FacebookApi extends Model {

	public $resourcesId;
	public $userId;

	/**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		
		$socialId = (new \yii\db\Query())
		    ->select('id')
		    ->from('type_resources')
		    ->where(['name' => 'Social media'])
		    ->one();
		
		
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Facebook','resourcesId' => $socialId['id']])
		    ->all();
		
		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

	/**
	 * [_getTwitterLogin get key to facebook]
	 * @return [type] [description]
	 */
	private function _getFacebookKey($userId){

		$credencials_api = (new \yii\db\Query())
		    ->select('api_secret_key,api_secret_key,bearer_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $this->resourcesId,'userId' => $this->userId])
		    ->all();
		if($credencials_api){
			$bearer_token = ArrayHelper::getColumn($credencials_api,'bearer_token')[0];
			if($bearer_token == ''){
				$api_key = ArrayHelper::getColumn($credencials_api,'api_key')[0];    
				$api_secret_key = ArrayHelper::getColumn($credencials_api,'api_secret_key')[0]; 
				$bearer_token = $this->_getBearerToken($api_key,$api_secret_key);
				if($bearer_token){
					$this->_setBearerToken($bearer_token);
				}
			}else{
				Codebird::setBearerToken($bearer_token);
				$this->codebird = Codebird::getInstance();
			} 
		}    

	}	

	function __construct($userId){
		
		// set userId 
		$this->userId = $userId;
		// set resource 
		$this->_setResourceId();
		// get facebook key
		$this->_getFacebookKey();
		// set limit
		//$this->products_count = $products_count;
		// call the parent __construct
		parent::__construct(); 
	}
}

?>