<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;


use yii\httpclient\Client;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * FacebookApi is the model behind the login API.
 *
 */
class FacebookCommentsApi extends Model {

	

	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;



	private $_baseUrl = 'https://graph.facebook.com';
	
	//private $_access_secret_token;
	
	private $_page_access_token;

	private $_client;
	

	/**
	 * [prepare params for the query]
	 * @param  [array] $alert [current alert]
	 * @return [array]        [array params]
	 */
	public function prepare($alert){
		if(!empty($alert)){
			$this->alertId    = $alert['id'];
			$this->userId     = $alert['userId'];
			$this->start_date = $alert['config']['start_date'];
			$this->end_date   = $alert['config']['end_date'];

			// checks is valid access_secret_token
			if(\app\helpers\FacebookHelper::isExpired($this->userId)){
				// send email notification
				echo 'Sending email notification to '. $$alert['userId'];
			}else{
				// get user credentials
				$userCredential = \app\helpers\FacebookHelper::getCredencials($this->userId);
				if(!is_null($userCredential)){
					// get page access token
					$this->_page_access_token = $this->_getPageAccessToken($userCredential->access_secret_token);
				}
				// prepare the query
				if(!is_null($this->_page_access_token)){
					/*$query = $this->_getQuery();
					$params['post_comment'] = $query;*/
				}
				
				return (isset($query)) ? $query : null;
			}
		}
		return false;
	}


	private function _setParams($alertId){

		$query = (new \yii\db\Query())
		    ->select(['since_id','max_id','date_searched','condition'])
		    ->from('alerts_mencions')
		    ->where([
				'alertId'       => $this->alertId,
				'resourcesId'   => $this->resourcesId,
				'type'          => 'comments',
				'term_searched' => $products[$p],
		    ])
		    ->one();

	}

	/**
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model = \app\models\AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => 'comments',
			'term_searched' => $properties['term_searched']
		])
		->one();

		if(is_null($model)){
			$model = new \app\models\AlertsMencions();
			$model->alertId = $this->alertId;
			$model->resourcesId = $this->resourcesId;
		}
		foreach($properties as $property => $values){
    		$model->$property = $values;
    	}
    	if(!$model->save()){
    		var_dump($model->errors);
    	}

	}

	/**
	 * [_getQuery get query form post and their comments]
	 * @param  [type] $end_date [description]
	 * @return [type]           [description]
	 */
	private function _getQueryPostComment($start_date;$end_date){
		$query = "https://graph.facebook.com/169441517247/posts?fields=from,full_picture,icon,is_popular,message,attachments{unshimmed_url},shares,created_time,comments{from,created_time,like_count,message,parent,comment_count,comments{likes.limit(10),comments{message},reactions{name}}}&until={$this->end_date}&since={$this->start_date}&limit=10&access_token={$this->_page_access_token}";

		return $query;
	}

	/**
	 * [_getPageAccessToken get page access token token]
	 * @param  [string] $access_secret_token [description]
	 * @return [string] [PageAccessToken]
	 */
	private function _getPageAccessToken($access_secret_token){
		$params = [
            'access_token' => $access_secret_token
        ];

        $page_access_token = null;
       
        try{
        	
        	$accounts = $this->_client->get('me/accounts',$params)->send();
        	$data = $accounts->getData();
        	$page_access_token = ArrayHelper::getColumn($data['data'],'access_token')[0]; 
        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($page_access_token)) ? $page_access_token : null;
	}


	private function _getClient(){
		$this->_client = new Client(['baseUrl' => 'https://graph.facebook.com']);
		return $this->_client;
	}

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
		    ->where(['name' => 'Facebook Comments','resourcesId' => $socialId['id']])
		    ->all();
		

		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];    
	}

	/**
	 * [_getTwitterLogin get key to facebook]
	 * @return [type] [description]
	 */
	/*private function _getFacebookKey($userId){

		$credencials_api = (new \yii\db\Query())
		    ->select('access_secret_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $this->resourcesId,'userId' => $this->userId])
		    ->one();
		if($credencials_api){
			// get _access_secret_token
			$this->_access_secret_token = ArrayHelper::getColumn($credencials_api,'access_secret_token')[0]; 
		}    

	}	*/

	function __construct(){
		
		// set resource 
		$this->_setResourceId();
		// get client
		$this->_getClient();
		
		parent::__construct(); 
	}
}

?>