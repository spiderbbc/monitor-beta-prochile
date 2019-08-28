<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\helpers\DateHelper;

use app\models\Alerts;
use app\models\file\JsonFile;
use app\models\AlertsMencions;

use Abraham\TwitterOAuth\TwitterOAuth;
use Codebird\Codebird;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * TwitterApi is the model behind the login API.
 *
 */
class TwitterApi extends Model {

	
	private $alertId;
	private $resourcesId;
	private $start_date;
	private $end_date;
	
	private $limit = 0;
	private $products_count;
	
	private $params = [
		'lang' => 'es',
		'result_type' => 'recent',
		'count' => 1,
	//	'q'      => '',
	//	'until'  => '',
	//	'max_id' => '',

	];
	private $codebird;
	private $data = [];

	/**
	 * [prepare set the property the alert for TwitterApi]
	 * @param  array  $alert  [the alert]
	 * @return [array]        [params for call twitter api]
	 */
	public function prepare($alert = []){
		if(!empty($alert)){
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];
			// prepare the products
			$products = $alert['products'];
			$products_params = $this->setProductsParams($products);
			return $products_params;
		}
		return false;
	}
	/**
	 * [setProductsParams set the params for each products in the alert]
	 * @param array $products [params for call api twitter]
	 */
	public function setProductsParams($products = []){
		$products_to_searched = [];
		for($p = 0; $p < sizeOf($products);$p++){
			$query = (new \yii\db\Query())
		    ->select(['date_searched', 'max_id','condition'])
		    ->from('alerts_mencions')
		    ->where([
				'alertId'       => $this->alertId,
				'resourcesId'   => $this->resourcesId,
				'type'          => 'tweet',
				'term_searched' => $products[$p],
		    ])
		    ->one();

		    // Make sure to urlencode any parameter values that contain query-reserved characters
		    $product_encode = urlencode($products[$p]);
		    
		    if($query){
		    	// insert params to the products with condicion active
		    	if($query['condition'] == AlertsMencions::CONDITION_ACTIVE){ 
		    		$this->params['q'] = $product_encode;
		    		$date_searched = DateHelper::add($query['date_searched'],'1 day');
					$this->params['until'] = $date_searched;
					$this->params['max_id'] = $query['max_id'];
		    		array_push($products_to_searched,$this->params);

		    	} 
		    }else{
		    	$this->params['q'] = $product_encode;
		    	$this->params['max_id'] = '';
				$date_searched = DateHelper::add($this->start_date,'1 day');
				$this->params['until'] = $date_searched;
		    	array_push($products_to_searched,$this->params);
		    }

		}
		return $products_to_searched;
		
	}	
	/**
	 * [call loop in to products and call method _getTweets]
	 * @param  array  $products_params [array of products_params]
	 * @return [type]                  [data]
	 */
	public function call($products_params = []){

		for($p = 0; $p < sizeOf($products_params); $p ++){
			$product_decode = urldecode($products_params[$p]['q']);
			$this->data[$product_decode] = $this->_getTweets($products_params[$p]);
		}
		var_dump($this->data);
	}

	/**
	 * [_getTweets for each param call api twitter]
	 * @param  [type] $params [params product]
	 * @return [type]         [data]
	 */
	private function _getTweets($params){
		
		$data    =[];
		$index   = 0;
		$limit   = 0;
		$sinceId = 0;
		$lastId  = 0;
      
        do {
        	$data[$index] = $this->search_tweets($params);

        	echo "httpstatus: ".$data[$index]['httpstatus']. "\n";
        	echo " is ".(empty($data[$index]['statuses'])) ? "empty". "\n": "no empty". "\n";
        	echo " the query ". $data[$index]['search_metadata']['query']. "\n";
        	echo "====================". "\n";        	
        	// is ok 200
        	if($data[$index]['httpstatus'] == 200 && !empty($data[$index]['statuses'])){
        		if(!$this->limit){
        			// set limit
        			$remaining = $data[$index]['rate']['remaining'];
        			$this->limit = $this->_setLimits($remaining);
        		}
        		//save the sinceId
        		$sinceId = $data[$index]['statuses'][0]['id'];
        		// get lastid
        		if(ArrayHelper::keyExists('next_results', $data[$index]['search_metadata'], true)){
        			parse_str($data[$index]['search_metadata']['next_results'], $output);
                    $params['max_id'] = $output['?max_id'];
                    $lastId = $output['?max_id'];
        		}

        		// check date validation
        		/*for($d = 0; $d < sizeOf($data[$index]); $d++) {
        			echo DateHelper::diffInDays($this);
        		}*/
        		
        		$this->limit --;
        		echo $this->limit  . "\n";
        		echo "====================". "\n";
        		echo "sinceId: ".$sinceId  . "\n";
        		echo "lastId: ".$lastId  . "\n";
        	}else{
        		break;
        	}

        }while($this->limit);

        return $sinceId;

	}
	
	/**
	 * [search_tweets call api search/tweet from the api]
	 * @param  array  $params [params to call twitter]
	 * @return [type]         [data]
	 */
	public function search_tweets($params = []){
		
		$this->codebird->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		$reply = $this->codebird->search_tweets($params, true);
		return $reply;
	}

	/**
	 * [_getProductSearched return from alert_mention table products with condition active o wait]
	 * @param  [type] $product [ej: HD]
	 * @return [type]          [query]
	 */
	private function _getProductSearched($product){
		
		$products_to_searched = [];
		$query = (new \yii\db\Query())
		    ->select(['date_searched', 'max_id','condicion'])
		    ->from('alerts_mencions')
		    ->where([
		    	'alertId' => $this->alertId,
				'resourcesId' => $this->resourcesId,
				//'condition' => AlertsMencions::CONDITION_ACTIVE,
				'type' => 'tweet',
				'term_searched' => $product,
		    ])
		    ->one();
		    if($query){
		    	if($query['condicion'] == AlertsMencions::CONDITION_FINISH){ return false;} 
		    }

		return $query;
	
	}
	/**
	 * [_setLimits divide the total number of limits by the quantity of products]
	 * @param [type] $remaining [description]
	 */
	private function _setLimits($remaining){
		$remaining = $remaining / $this->products_count;
		
		return round($remaining);
	}
	/**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Twitter'])
		    ->all();
		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];
	}
	/**
	 * [_getTwitterLogin login to twitter]
	 * @return [type] [description]
	 */
	private function _getTwitterLogin(){

		$credencials_api = (new \yii\db\Query())
		    ->select('api_key,api_secret_key,bearer_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $this->resourcesId])
		    ->all();
		if($credencials_api){
			$key = Yii::$app->params['key'];
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
	/**
	 * [_getBearerToken get the bearer_token]
	 * @param  [type] $api_key        [description]
	 * @param  [type] $api_secret_key [description]
	 * @return [type]                 [description]
	 */
	private function _getBearerToken($api_key,$api_secret_key){
		$bearer_token = false;

		$key = Yii::$app->params['key'];

		$api_key = Yii::$app->getSecurity()->decryptByPassword(utf8_decode($api_key), $key);
		$api_secret_key = Yii::$app->getSecurity()->decryptByPassword(utf8_decode($api_secret_key), $key);
		
		Codebird::setConsumerKey($api_key, $api_secret_key); // static, see README
		$this->codebird = Codebird::getInstance();
		$reply = $this->codebird->oauth2_token();
		$bearer_token = $reply->access_token;
		
		return $bearer_token;

	}
	/**
	 * [_setBearerToken set bearer_token in the database]
	 * @param [type] $bearer_token [description]
	 */
	private function _setBearerToken($bearer_token){
		
		$secretKey = Yii::$app->params['key'];
		// INSERT (table name, column values)
		Yii::$app->db->createCommand()->update('credencials_api', [
		    'bearer_token' => $bearer_token,
		],'resourceId = 1')->execute();
	}


	function __construct($products_count = 0){
		// set resource 
		$this->_setResourceId();
		// get twitter login api
		$this->_getTwitterLogin();
		// set limit
		$this->products_count = $products_count;
		// call the parent __construct
		parent::__construct(); 
	}
}

?>