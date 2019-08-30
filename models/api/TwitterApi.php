<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

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
		// forming the array params
		$params = [
			'lang' => 'es',
			'result_type' => 'recent',
			'count' => 100,
		//	'q'      => '',
		//	'until'  => '',
		//	'max_id' => '',

		];
		
		for($p = 0; $p < sizeOf($products);$p++){
			$query = (new \yii\db\Query())
		    ->select(['since_id','max_id','date_searched','condition'])
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
		    		// pass to variable
		    		list('since_id' => $since_id, 'max_id' => $max_id,'date_searched' => $date_searched) = $query;
		    		
					$params['q']      = $product_encode;
					$params['until']  = DateHelper::add($date_searched,'1 day');
					$params['max_id'] = $max_id;

		    		if(($since_id == 0) && ($max_id == 0)){
		    			$date_searched = DateHelper::add($query['date_searched'],'1 day');
						$this->params['until'] = $date_searched;
		    		}

		    		/*if(($since_id) && ($max_id == 0)){
						$this->params['since_id'] = $since_id;
		    		}*/

		    		/*if(($since_id) && ($max_id)){
		    			$this->params['since_id'] = $since_id;
		    			$this->params['max_id'] = '';
		    			$this->params['until'] = '';
						//$this->params['max_id'] = $max_id;
		    		}
*/
		    		array_push($products_to_searched,$params);

		    		/*$this->params['q'] = $product_encode;
		    		$date_searched = DateHelper::add($query['date_searched'],'1 day');
					$this->params['until'] = $date_searched;
					$this->params['max_id'] = $query['max_id'];
		    		array_push($products_to_searched,$this->params);*/

		    	} 
		    }else{
				$date_searched = DateHelper::add($this->start_date,'1 day');
		    	$params['max_id'] = '';
		    	//$params['since_id'] = '';
				$params['until'] = $date_searched;
		    	$params['q'] = $product_encode;
		    	array_push($products_to_searched,$params);
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
			Console::stdout("loop in call method {$product_decode}.. \n", Console::BOLD);
			$this->data[$product_decode] = $this->_getTweets($products_params[$p]);
		}
		return $this->data;
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
		$sinceId = null;
		$max_id  = null;

		$properties = [
	      'term_searched' => $params['q'],
	      'type' => 'tweet',
	    ];
      
        do {
        	// get data twitter api
        	$data[$index] = $this->search_tweets($params);
        	/*var_dump($params);*/
      	
        	// is ok 200
        	if(($data[$index]['httpstatus'] == 200) && (!empty($data[$index]['statuses']))){
        		// check limits
        		if(!$this->limit){
        			// set limit
        			$remaining = $data[$index]['rate']['remaining'];
        			$this->limit = $this->_setLimits($remaining);
        		}
        		

        		// check date validation
        		$date_searched = DateHelper::sub($params['until'],'1 day');
        		// looping to see if we go over the search date
        		$lantern = false;
        		for($s = 0; $s < sizeOf($data[$index]['statuses']); $s++) {
        			$firts_date = $data[$index]['statuses'][$s]['created_at'];
        			$diff = DateHelper::diffInDays($date_searched, $firts_date);
        			if($diff){
	    				$now = Yii::$app->formatter->asDate('now', 'yyyy-MM-dd'); 
	    				$is_today_search = DateHelper::diffInDays($firts_date,$now);
	    				if($is_today_search){
	    					$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
	    					$this->_saveAlertsMencions($properties);
	    				}else{
	    					$properties['date_searched'] = Yii::$app->formatter->asTimestamp($params['until']);
	    					$this->_saveAlertsMencions($properties);
	    				}
	    				$lantern = true;
	    				unset($data[$index]['statuses'][$s]);
	    				// We have to get out of here
	    				break;
	    			}
        		}
    			
    			// get out lantern
    			if($lantern){break;}
    			
    			
    			
        		
        		// get next_results
        		if(ArrayHelper::keyExists('next_results', $data[$index]['search_metadata'], true)){
        			// clean next result
        			parse_str($data[$index]['search_metadata']['next_results'], $output);
					
					$params['max_id'] = $output['?max_id']  - 1;
					$lastId           = $output['?max_id'];

					// we are over the limit
	        		if($this->limit == 50){
	        			$properties['max_id'] = $lastId;
	        			$date_searched = DateHelper::sub($params['until'],'1 day');
	        			$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
	              		$this->_saveAlertsMencions($properties);
	        		}
	        		//only for testing
	        		if($this->limit == 50){break;}

        		}else{
        			// if not result Looking for the next date
	        		$date_searched = DateHelper::add($params['until'],'1 day');
	        		$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
	              	$this->_saveAlertsMencions($properties);
	        		break;

        		}

        		// add index
        		$index++;
        		// sub limit
        		$this->limit --;
        		echo "====================". "\n";
        		echo $params['q']  . "\n";
        		echo $this->limit  . "\n";
        		echo "====================". "\n";


        	}else{
        		// if not result Looking for the next date
        		$date_searched = DateHelper::add($params['until'],'1 day');
        		$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
              	$this->_saveAlertsMencions($properties);
        		break;
        	}

        }while($this->limit);

        return $data;

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
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model = AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => 'tweet',
			'term_searched' => $properties['term_searched']
		])
		->one();

		if(is_null($model)){
			$model = new AlertsMencions();
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