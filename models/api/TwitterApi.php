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
		'result_type' => 'mixed',
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
		    $product = urlencode($products[$p]);
		    
		    if($query){
		    	// insert params to the products with condicion active
		    	if($query['condition'] == AlertsMencions::CONDITION_ACTIVE){ 
		    		// pass to variable
		    		list('since_id' => $since_id,'max_id' => $max_id,'date_searched' => $date_searched) = $query;
		    		
		    		$since_date = Yii::$app->formatter->asDatetime($date_searched,'yyyy-MM-dd');
					$until_date = DateHelper::add($date_searched,'1 day');
		    		$query_search = "{$product} since:{$since_date} until:{$until_date}";

					$is_date_searched_higher_to_end_date = DateHelper::diffForHumans($date_searched,$this->end_date);
					$is_higher_to_end_date = explode(" ",$is_date_searched_higher_to_end_date);

					if($is_higher_to_end_date[2] == "before"){
						
						
						$params['q']       = $query_search;
						$params['max_id']  = $max_id;

						$params['since']   = $since_date;
						$params['until']   = $until_date;
						
					}

					if($since_id){
						$params['q']        = $query_search;
						$params['since_id'] = $since_id;
						$params['since']    = $since_date;
						$params['until']    = $until_date;
					}
					

					$params['product'] = $products[$p];
					array_push($products_to_searched,$params);
		    	} 
		    }else{
				$since_date = Yii::$app->formatter->asDatetime($this->start_date,'yyyy-MM-dd');
				$until_date = DateHelper::add($this->start_date,'1 day');
		    	$query_search = "{$product} since:{$since_date} until:{$until_date}";
		    	
		    	$params['q'] = $query_search;
		    	$params['since'] = $since_date;
		    	$params['until'] = $until_date;
		    	
		    	$params['product'] = $products[$p];
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
			$product = $products_params[$p]['product'];
			Console::stdout("loop in call method {$product}.. \n", Console::BOLD);
			$this->data[$product] = $this->_getTweets($products_params[$p]);
		}
		$data = $this->_orderTweets($this->data);
		return $this->data;
	}

	/**
	 * [_getTweets for each param call api twitter]
	 * @param  [type] $params [params product]
	 * @return [type]         [data]
	 */
	private function _getTweets($params){
		
		$data   =[];
		$index  = 0;
		$limit  = 0;
		$sinceId  = null;
		$since_date  = null;
		$until_date = null;
		$max_id = null;

		
      
      	$product = ArrayHelper::remove($params, 'product');
      	$since_date = ArrayHelper::remove($params, 'since');
      	$until_date =  ArrayHelper::remove($params, 'until');


      	$properties = [
	      'term_searched' => $product,
	      'type' => 'tweet',
	    ];
	    
      	var_dump($params);

        do {
        	// get data twitter api
        	$data[$index] = $this->search_tweets($params);
        	
        	// is ok 200
        	if($data[$index]['httpstatus'] == 200){
        		// if statuses not empty
        		if(!empty($data[$index]['statuses'])){
        			// check limits
	        		if(!$this->limit){
	        			// set limit
	        			$remaining = $data[$index]['rate']['remaining'];
	        			$this->limit = $this->_setLimits($remaining);
	        		}
	        		
	        		//save the sinceId one time for product
            
		            if(is_null($sinceId)){
		              $sinceId = $data[$index]['statuses'][0]['id'] + 1;
		              Console::stdout("save one time {$sinceId}.. \n", Console::BOLD);
		            }

	        		// get next_results
	        		if(ArrayHelper::keyExists('next_results', $data[$index]['search_metadata'], true)){
	        			// clean next result
	        			parse_str($data[$index]['search_metadata']['next_results'], $output);
						
						$params['max_id'] = $output['?max_id']  - 1;
						$lastId           = $output['?max_id'];

						// we are over the limit
		        		if($this->limit == 1){
		        			$properties['max_id'] = $lastId;
		        			$date_searched = $since_date;
		        			$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
		              		$this->_saveAlertsMencions($properties);
		        		}
		        		//only for testing
		        		if($this->limit == 1){break;}

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
        			Console::stdout("there is empty statuses  \n", Console::BOLD);

        			$properties['max_id'] = '';
        			if(DateHelper::isToday($since_date)){
        				$properties['since_id'] = $sinceId;
        				$date_searched = $since_date;
        			}else{
        				$date_searched = DateHelper::add($since_date,'1 day');
        			}
        			$properties['date_searched'] = Yii::$app->formatter->asTimestamp($date_searched);
	              	$this->_saveAlertsMencions($properties);
	        		break;
        		}
        	}else{
        		// problem with api :/
        		echo "====================". "\n";
        		echo "Api problem : ".$data[$index]['httpstatus']. "\n";
        		echo "====================". "\n";
        		// lets go
        		break;
        		
        	}

        }while($this->limit);
        Console::stdout("return	 data.. \n", Console::BOLD);	
        return $data;

	}
	
	/**
	 * [search_tweets call api search/tweet from the api]
	 * @param  array  $params [params to call twitter]
	 * @return [type]         [data]
	 */
	public function search_tweets($params = []){
		sleep(1);
		$this->codebird->setReturnFormat(CODEBIRD_RETURNFORMAT_ARRAY);
		ini_set('memory_limit', '800M');  // 
		return $this->codebird->search_tweets($params, true);
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
	 * [_orderTweets description]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	private function _orderTweets($data){
		$tweets = [];
		$source = 'TWITTER';
	
		foreach ($data as $product => $object){
			$index = 0;
			for ($o = 0; $o < sizeof($object) ; $o++){
				if(!empty($object[$o]['statuses'])){
					for ($s =0; $s < sizeof($object[$o]['statuses']) ; $s++){
						$tweets[$product][$index]['source'] = $source;
						
						
						if(isset($object[$o]['statuses'][$s]['entities']['urls'][0])){
							$tweets[$product][$index]['url'] = $object[$o]['statuses'][$s]['entities']['urls'][0]['url'];
						}else{
							$tweets[$product][$index]['url'] = '-';
						}

						if(array_key_exists('place', $object[$o])){
							if(!is_null($object[$o]['place'])){
								$tweets[$product][$index]['location'] = $object[$o]['place']['country'];
							}
						}else{
							$tweets[$product][$index]['location'] = "-";
						}
						
						$tweets[$product][$index]['created_at'] = $object[$o]['statuses'][$s]['created_at'];
						$tweets[$product][$index]['author_name'] = $object[$o]['statuses'][$s]['user']['name'];
						$tweets[$product][$index]['author_username'] = $object[$o]['statuses'][$s]['user']['screen_name'];
						$tweets[$product][$index]['followers_count'] = $object[$o]['statuses'][$s]['user']['followers_count'];
						$tweets[$product][$index]['post_from'] = $object[$o]['statuses'][$s]['text'];
						$index++;
					} // for each statuses
				} // if not empty statuses
			}// for each object twitter
		} // for each product

		return $tweets;
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