<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

use yii\httpclient\Client;

use app\models\file\JsonFile;


/**
 * FacebookApi is the model behind the login API.
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */
class FacebookCommentsApi extends Model {

	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	public $products;
	public $data;
	private $_baseUrl = 'https://graph.facebook.com/v4.0';
	
	private $_limit_post = 1;
	private $_limit_commets = 5;
	
	
	private $_page_access_token;
	private $_appsecret_proof;
	private $_bussinesId;
	private $_client;
	private $resourceName = 'Facebook Comments';
	

	/**
	 * [prepare params for the query]
	 * @param  [array] $alert [current alert]
	 * @return [array]        [array params]
	 */
	public function prepare($alert){
		
		if(!empty($alert)){
			// set variables
			$this->alertId    = $alert['id'];
			$this->userId     = $alert['userId'];
			$this->start_date = $alert['config']['start_date'];
			$this->end_date   = $alert['config']['end_date'];
			$this->_bussinesId = (empty($alert['config']['product_description'])) ? Yii::$app->params['facebook']['business_id'] : $alert['config']['product_description'];
			// reset data
			$this->data = [];

			//get from alermentios
			$alertsMencions = \app\models\AlertsMencions::find()->where([
	    		'alertId'       => $this->alertId,
		        'resourcesId'   => $this->resourcesId,
		        'type'        	=> 'comments',
	    	])->all();

	    	if (count($alertsMencions)) {
	    		$products = [];
	    		foreach ($alertsMencions as $alertsMencion) {
	    			if(in_array($alertsMencion->term_searched, $alert['products'])){
	    				$index = array_search($alertsMencion->term_searched, $alert['products']);
	    				unset($alert['products'][$index]);
	    			}
	    		}

	    		$alert['products'] = array_values($alert['products']);

	    	}
			////
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->products   = $alert['products'];

			if (count($this->products)) {
				return $this->_setParams();
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	 * [_setParams set params to build the call]
	 */
	private function _setParams(){

		$params = [];
		// get the user credentials
		$user_credential = \app\helpers\FacebookHelper::getCredencials($this->userId);
		// get page token   
		$this->_page_access_token = $this->_getPageAccessToken($user_credential);
		// get appsecret_proof
		$this->_appsecret_proof = $this->_getAppsecretProof($this->_page_access_token);
		// loading firts query
		$params['query'] = $this->_postCommentsSimpleQuery();  

		return $params; 

	}


	/**
	 * [call loop in to for each alert and call method _getComments]
	 * @param  array  $query_params   [array of query]
	 * @return [type]                  [data]
	 */
	public function call($query_params = []){

		$data = $this->_getDataApi($query_params);

		if($data){
			$this->data[] = $this->_orderDataByProducts($data);

			
		}
		$this->searchFinish();
		
	}

	/**
	 * [_getDataApi call methods to get data from api facebook]
	 * @param  [array] $query_params [all posts and comments]
	 * @return [array] $responseData [ post/comments]
	 */
	private function _getDataApi($query_params){

		$feeds = $this->_getPostsComments($query_params);
		
		$feedsCandidate = $this->_setCandidate($feeds);

		$feeds_comments = $this->_getComments($feedsCandidate);
		$feeds_reviews = $this->_getSubComments($feeds_comments);
		$model = $this->_orderFeedsComments($feeds_reviews);

		
		return $model;

			
	}

	/**
	 * [_getPostsComments call API to get post on facebook]
	 * @param  [array] $query_params [all posts and comments]
	 * @return [array] $responseData [ post/comments]
	 */
	private function _getPostsComments($query_params){
		$client = $this->_client;
		// simple query
		if(\yii\helpers\ArrayHelper::keyExists('query', $query_params, false) ){

			$after = '';
			$index = 0;
			$responseData = [];
			// lets loop if next in post or comments and there limit facebook	
			do {
				
				try{
					
					$posts = $client->get($query_params['query'],[
						'after' => $after,
						'access_token' => $this->_page_access_token,
						'appsecret_proof' => $this->_appsecret_proof
					])
					->setOptions([
			        //'proxy' => 'tcp://proxy.example.com:5100', // use a Proxy
			        'timeout' => 15, // set timeout to 5 seconds for the case server is not responding
			    	])->send();
					
					
					$responseHeaders = $posts->headers->get('x-business-use-case-usage'); // get headers
					// is over the limit
					if(\app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_bussinesId)){
						break;
					}

					// if get error data
					if(\yii\helpers\ArrayHelper::getValue($posts->getData(),'error' ,false)){
						// send email with data $responseData[$index]['error']['message']
						break;
					}
					
					// get the after
					if(\yii\helpers\ArrayHelper::getValue($posts->getData(),'paging.cursors.after' ,false)){ // if next
						$after = \yii\helpers\ArrayHelper::getValue($posts->getData(),'paging.cursors.after' ,false);
					} 

					$data =  $posts->getData(); // get all post and comments
					
					$is_next = (empty($data['data'])) ? false : true;

					if(isset($data['data'][0]['comments']['data'])){
						$responseData[$index] = $data;
						$index++;
					}
					

				}catch(\yii\httpclient\Exception $e){
					// send a email with no internet connection
					 echo 'Excepción capturada: ',  $e->getMessage(), "\n";
					 die();
				}
			
			}while($is_next);
		
			
			return $responseData;
		}
	}

	/**
	 * [_getComments call comments if there and loop by paginations]
	 * @param  [array] $feeds [all posts and comments]
	 * @return [array] $feeds [ post/comments]
	 */
	private function _getComments($feeds){
		$client = $this->_client;
		 
		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}

		// for each pagination
		for($p = 0; $p < sizeOf($feeds); $p++){
			if(isset($feeds['data'])){
				// for each feed is limit is one
				for($d=0; $d < sizeOf($feeds[$p]['data']); $d++){
					// take id post
					$id_feed = $feeds[$p]['data'][$d]['id'];
					// if there comments
					if(isset($feeds[$p]['data'][$d]['comments'])){
						// if there next
						if(isset($feeds[$p]['data'][$d]['comments']['paging']['next'])){
							
							$next = $feeds[$p]['data'][$d]['comments']['paging']['next'];
							/**
							 * TODO TEST
							 */
							// if there next in the database
							if(isset($params)){
								if (ArrayHelper::keyExists($id_feed, $params['feeds'], false)) {
									if($params['feeds'][$id_feed]['next'] != ''){
										$next = $params['feeds'][$id_feed]['next'];
										// clean next in the database
										$where['publication_id'] = $id_feed;
										\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['next' => null]);
									}
								}
							}
							
							$comments = [];

							do{

								$commentsResponse = $client->get($next)->send();// more comments then

								$comments =  $commentsResponse->getData(); // get all post and comments

								$responseHeaders = $commentsResponse->headers->get('x-business-use-case-usage'); // get headers
								// if get error data
	                            if(\yii\helpers\ArrayHelper::getValue($comments,'error' ,false)){
	                                // send email with data $responseData[$index]['error']['message']
	                                break;
	                            }
	                            // get the after
	                            if(\yii\helpers\ArrayHelper::getValue($comments,'paging.next' ,false)){ // if next
	                                $next = \yii\helpers\ArrayHelper::getValue($comments,'paging.next' ,false);
	                                $is_next = true;
	                            }else{
	                                $is_next = false;
	                            } 

	                            // is over the limit
	                            $is_usage_limit = \app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_bussinesId);


	                            if($is_usage_limit){
									// save the next 
									if($next){
										$where['publication_id'] = $id_feed;
								       // Console::stdout("save one time {$next}.. \n", Console::BOLD);
								        $model_alert = \app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['next' => $next]);
									}
								}
	                            
	                            // if there more comments
	                            if(!empty($comments['data'])){
	                            	for($n = 0; $n < sizeOf($comments['data']); $n++){
	                            		$feeds[$p]['data'][$d]['comments']['data'][] =$comments['data'][$n];
	                            	}
	                            }
	                            // is over the limit
								if($is_usage_limit){
									break;
								}
								

	                           // $index++;

							}while($is_next);


						}
						
					}

					//last comments
				}
			}
			
		}

		// coment because get likes
		/*if(isset($params)){
			$feeds = $this->_isLastComments($feeds,$params);
		}*/
	


		return $feeds;
		
	}

	/**
	 * [_isLastComments call check is last comment compair last record on db : depred]
	 * @param  depred
	 * @return depred
	 */
	private function _isLastComments($feeds,$params){
		
		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}



		for ($p=0; $p < sizeOf($feeds); $p++){
			if(isset($feeds[$p]['data'])){
				for($d=0; $d < sizeOf($feeds[$p]['data']); $d++){
					// take id post
					$id_feed = $feeds[$p]['data'][$d]['id'];
					if(ArrayHelper::keyExists($id_feed,$params['feeds'])){
						$comments_last = [];
						for ($c=0;$c < sizeOf($feeds[$p]['data'][$d]['comments']['data']); $c++){
							
							$created_time = $feeds[$p]['data'][$d]['comments']['data'][$c]['created_time'];
							$unix_time = \app\helpers\DateHelper::asTimestamp($created_time);
							
							if($unix_time > $params['feeds'][$id_feed]['max_id']){
								$comments_last[] = $feeds[$p]['data'][$d]['comments']['data'][$c];
								$where['publication_id'] =  $id_feed;
								
								// add plus a second to the max_id
								$unix_time = strtotime("+5 seconds",$unix_time);
								\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['max_id' => $unix_time,'publication_id' => $id_feed]);
							}

						}
						// check if data
						$feeds[$p]['data'][$d]['comments']['data'] = $comments_last;
					}
				}
			}
		}

		return $feeds;
	}

	/**
	 * [_getSubComments call sub comments if there and loop by paginations]
	 * @param  [array] $feeds_comments [all posts and comments]
	 * @return [array] $feeds_comments [ post/comments]
	 */
	private function _getSubComments($feeds_comments){
		$client = $this->_client;

		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];

		$query = \app\helpers\AlertMentionsHelper::getAlersMentions($where);
		if($query){
			$params['feeds'] = ArrayHelper::index($query,'publication_id');
		}
		


		// for each pagination
		for($p = 0; $p < sizeOf($feeds_comments); $p++){
			// for each data
			if(isset($feeds_comments[$p]['data'])){
				for($d=0; $d < sizeOf($feeds_comments[$p]['data']); $d++){

					$lasted_update = $feeds_comments[$p]['data'][$d]['updated_time'];
					$id_feed = $feeds_comments[$p]['data'][$d]['id'];


					

					// if there comments
					if(isset($feeds_comments[$p]['data'][$d]['comments'])){

						// loop in comments
						for($c=0; $c < sizeOf($feeds_comments[$p]['data'][$d]['comments']['data']); $c++){
							// IF THERE SUBCOMMENTS
							if(isset($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments'])){
								//echo 'its data..';
								// loop through subcomments
								for($s=0; $s < sizeOf($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data']); $s++){
									
									$id_message = $feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data'][$s]['id'];

									//echo $id_message. "\n";
									
									$commentsResponse = $client->get($id_message,[
										'access_token' => $this->_page_access_token
									])->send();// more comments then
									
									// if get error data
		                            if(\yii\helpers\ArrayHelper::getValue($commentsResponse->getData(),'error' ,false)){
		                                // send email with data $responseData[$index]['error']['message']
		                                break;
		                            }

		                            $responseHeaders = $commentsResponse->headers->get('x-business-use-case-usage'); // get headers
		                            // if over the limit
		                            if(\app\helpers\FacebookHelper::isCaseUsage($responseHeaders,$this->_bussinesId)){
		                            	break;
		                            }


									array_push($feeds_comments[$p]['data'][$d]['comments']['data'][$c]['comments']['data'][$s],$commentsResponse->getData());
								}
							}
						}
					}	
					
					if(!\app\helpers\AlertMentionsHelper::isAlertsMencionsExists($id_feed,$this->alertId)){
						$unix_time = \app\helpers\DateHelper::asTimestamp($lasted_update);
						// add plus a second to the max_id
						$unix_time = strtotime("+60 seconds",$unix_time);
						$where['publication_id'] =  $id_feed;
						\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['max_id' => $unix_time,'publication_id' => $id_feed]);
					}
				}

			}

		}


		return $feeds_comments;
	}
	/**
	 * [_orderFeedsComments return order post and his comments]
	 * @param  [array] $feeds_reviews [all posts and comments]
	 * @return [array] $model [ post/comments]
	 */
	private function _orderFeedsComments($feeds_reviews){

		$model = [];
		for($p = 0; $p < sizeOf($feeds_reviews); $p++){
			if(!empty($feeds_reviews[$p]) && isset($feeds_reviews[$p]['data'])){
				for($d=0; $d < sizeOf($feeds_reviews[$p]['data']); $d++){
					// get post data
					$model[$p]['id'] = $feeds_reviews[$p]['data'][$d]['id'];
					// from
					$model[$p]['from'] = $feeds_reviews[$p]['data'][$d]['from']['name'];
					// is popular
					$model[$p]['is_popular'] = $feeds_reviews[$p]['data'][$d]['is_popular'];
					//shares
					if(isset($feeds_reviews[$p]['data'][$d]['shares'])){
						$model[$p]['shares'] = $feeds_reviews[$p]['data'][$d]['shares'];
					}else{
						$model[$p]['shares'] = 0;
					}
					// full_picture
					if(isset($feeds_reviews[$p]['data'][$d]['full_picture'])){
						$model[$p]['picture'] = $feeds_reviews[$p]['data'][$d]['full_picture'];
					}else{
						$model[$p]['picture'] = "-";
					}
					// attachments
					if(isset($feeds_reviews[$p]['data'][$d]['attachments'])){
						$model[$p]['unshimmed_url'] = $feeds_reviews[$p]['data'][$d]['attachments']['data'][0]['unshimmed_url'];
					}else{
						$model[$p]['attachments'] = "-";
					}
					
					if(isset($feeds_reviews[$p]['data'][$d]['message'])){
						// remove emoji
						//$message  = \app\helpers\StringHelper::remove_emoji($feeds_reviews[$p]['data'][$d]['message']);
						// remove accent
						$message  = \app\helpers\StringHelper::replaceAccents($feeds_reviews[$p]['data'][$d]['message']);
						$model[$p]['message'] = $message;
					}else{
						$model[$p]['message'] = "-";
					}
					
					$model[$p]['created_time'] = $feeds_reviews[$p]['data'][$d]['created_time'];
					$model[$p]['updated_time'] = $feeds_reviews[$p]['data'][$d]['updated_time'];
					// get comments
					if(isset($feeds_reviews[$p]['data'][$d]['comments'])){
						$comments = $feeds_reviews[$p]['data'][$d]['comments'];
						$model[$p]['comments'] = $this->_orderComments($comments); 
					}
				}
			}
		}

		return $model;
	}
	/**
	 * [_orderComments return order comments data]
	 * @param  [array] $comments [all comments]
	 * @return [array] $data [ comments]
	 */
	private function _orderComments($comments){

		$data = [];
		$index = 0;
		for($c=0; $c < sizeOf($comments['data']); $c++){
			if(!\app\helpers\StringHelper::isEmpty($comments['data'][$c]['message'])){

				if(\app\helpers\DateHelper::isBetweenDate($comments['data'][$c]['created_time'],$this->start_date,$this->end_date)){

					$data[$index]['id'] = $comments['data'][$c]['id'];
					$data[$index]['created_time'] = $comments['data'][$c]['created_time'];
					$data[$index]['like_count'] = $comments['data'][$c]['like_count'];
					$data[$index]['permalink_url'] = $comments['data'][$c]['permalink_url'];
					// remove emoji 
					//$message = \app\helpers\StringHelper::remove_emoji($comments['data'][$c]['message']);
					// remove accent
					$message = \app\helpers\StringHelper::replaceAccents($comments['data'][$c]['message']);

					$data[$index]['message'] = $message;
					$data[$index]['message_markup'] = $message;

					
					if(isset($comments['data'][$c]['comments'])){

						for($s= 0; $s < sizeOf($comments['data'][$c]['comments']['data']); $s++){

							if (isset($comments['data'][$c]['comments']['data'][$s][0]['created_time'])) {
								if(\app\helpers\DateHelper::isBetweenDate($comments['data'][$c]['comments']['data'][$s][0]['created_time'],$this->start_date,$this->end_date)){

									$index ++;
									$data[$index]['id'] = $comments['data'][$c]['comments']['data'][$s][0]['id'];
									$data[$index]['created_time'] = $comments['data'][$c]['comments']['data'][$s][0]['created_time'];
									if(isset($comments['data'][$c]['comments']['data'][$s][0]['permalink_url'])){
										$data[$index]['permalink_url'] = $comments['data'][$c]['comments']['data'][$s][0]['permalink_url'];
									}
									if(isset($comments['data'][$c]['comments']['data'][$s][0]['like_count'])){
										$data[$index]['like_count'] = $comments['data'][$c]['comments']['data'][$s][0]['like_count'];	
									}
									
									// remove emoji
									//$coment = \app\helpers\StringHelper::remove_emoji($comments['data'][$c]['comments']['data'][$s][0]['message']);
									$coment = \app\helpers\StringHelper::replaceAccents($comments['data'][$c]['comments']['data'][$s][0]['message']);

									$data[$index]['message'] = $coment;
									$data[$index]['message_markup'] = $coment;

								}
							}
						}
					}
					$index ++;

				}

			}			
		}
		return $data;
	}
	/**
	 * [_orderDataByProducts return only post when his title check with the term to search]
	 * @param  [array] $data [all posts]
	 * @return [array] $feeds_candidate [ post filter by term]
	 */
	private function _orderDataByProducts($data){
		$model = [];
		$feed_count = count($data);
		$data = array_values($data);

		// params to save in AlertMentionsHelper and get
		$where = [
			'condition'   => 'ACTIVE',
			'type'        => 'comments',
			'alertId'     => $this->alertId,
			'resourcesId' => $this->resourcesId,
		];
		

		for($d = 0 ; $d < sizeOf($data); $d++){
			for($p = 0; $p < sizeof($this->products); $p++){
				// destrutura el product
				$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$p]);
				
				// get message
				$sentence = $data[$d]['message'];
				// get url
				$url  = $data[$d]['unshimmed_url'];
				$id_feed = $data[$d]['id'];
				// get share
				$share_feed['shares'] = (isset($data[$d]['shares']['count'])) ? $data[$d]['shares']['count'] : 0;
				
				$date = \app\helpers\DateHelper::asTimestamp($data[$d]['created_time']);

				$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($sentence,$product_data) : \app\helpers\StringHelper::containsAll($sentence,$product_data);
				// if containsAny
				if($is_contains){
					if($feed_count){
						// if a not key
						if(!ArrayHelper::keyExists($this->products[$p], $model, false)){
							$model [$this->products[$p]] = [] ;

						}
						// if not value
						if(!in_array($data[$d],$model[$this->products[$p]])){

							$where['publication_id'] = $id_feed;
							
							\app\helpers\AlertMentionsHelper::saveAlertsMencions($where,['term_searched' => $this->products[$p],'date_searched' => $date,'title' => $sentence, 'url' => $url,'mention_data' => $share_feed]);
							$model[$this->products[$p]][] = $data[$d];
							
							$feed_count --;
							break;
						}
					}
				}
			}

		}

		return $model;

	}

	/**
	 * [_setCandidate return only post when his title check with the term to search]
	 * @param  [array] $feeds [all posts]
	 * @return [array] $feeds_candidate [ post filter by term]
	 */
	private function _setCandidate($feeds){
		$feeds_candidate = [];
		
		// for each pagination
		for($p = 0; $p < sizeOf($feeds); $p++){
			// for each feed is limit is one
			$feeds_candidate[$p] =[];
			for($d=0; $d < sizeOf($feeds[$p]['data']); $d++){
				if(isset($feeds[$p]['data'][$d]['message'])){
					$feeds_candidate[$p]['data'] =[];
					for($i = 0; $i < sizeof($this->products); $i++){
						// take sentence post
						$sentence = $feeds[$p]['data'][$d]['message'];
						// destrutura el product
						$product_data = \app\helpers\StringHelper::structure_product_to_search($this->products[$i]);
						$is_contains = (count($product_data) > 3) ? \app\helpers\StringHelper::containsAny($sentence,$product_data) : \app\helpers\StringHelper::containsAll($sentence,$product_data);
						if($is_contains){
							if(!in_array($feeds[$p]['data'][$d],$feeds_candidate[$p]['data'])){
								$feeds_candidate[$p]['data'][] = $feeds[$p]['data'][$d];
							}
						}
					}// end loop products
				}
			}// end loop data
		}// end loop pagination		
		return $feeds_candidate;
	}
	/**
	 * [saveJsonFile save the content on json file]
	 * @return avoid
	 */
	public function saveJsonFile(){
		$source = 'Facebook Comments';
		if(!is_null($this->data)){
			foreach ($this->data as $data){
				foreach($data as $product => $feed){
					$jsonfile = new JsonFile($this->alertId,$source);
					$jsonfile->load($data);
				}
				$jsonfile->save();
			}
		}

	}
	/**
	 * [searchFinish checks if this resources is finish]
	 * @return avoid
	 */
	private function searchFinish()
	{
		$model = [
            'Facebook Comments' => [
                'resourceId' => $this->resourcesId,
                'status' => 'Pending'
            ]
        ];

        $today = \app\helpers\DateHelper::getToday();
        $end_date = strtotime(\app\helpers\DateHelper::add($this->end_date,'1 day'));

        if($today >= $end_date){
        	$alermentions = \app\models\AlertsMencions::find()->where([
        		'alertId' => $this->alertId,
        		'resourcesId' => $this->resourcesId,
        		'type' => 'comments'
        	])->all();
        	
        	if (count($alermentions)) {
        		foreach ($alermentions as $alermention) {
	        		$alermention->condition = 'INACTIVE';
	        		$alermention->save();
	        	}
        	}
        	$model['Facebook Comments']['status'] = 'Finish'; 
        }

        \app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);
		

	}

	/**
	 * [_getPageAccessToken get page access token token]
	 * @param  [string] $access_secret_token [description]
	 * @return [string] [PageAccessToken]
	 */
	private function _getPageAccessToken($user_credential){
		
		$appsecret_proof = $this->_getAppsecretProof($user_credential->access_secret_token);
		$params = [
            'access_token' => $user_credential->access_secret_token,
            'appsecret_proof' => $appsecret_proof
        ];

        $page_access_token = null;
       
        try{
        	
        	$accounts = $this->_client->get('me/accounts',$params)->send();
        	$data = $accounts->getData();
        	if(isset($data['error'])){
        		// to $user_credential->user->username and $user_credential->name_app
        		// error send email with $data['error']['message']
        		return null;
        	}
			$page_access_token = ArrayHelper::getColumn($data['data'],'access_token')[0]; 
			 

        }catch(\yii\httpclient\Exception $e){
        	// problem conections
        	// send a email
        }
        

        return (!is_null($page_access_token)) ? $page_access_token : null;
	}
	/**
	 * [_getAppsecretProof get AppsecretProof by  access_token]
	 * @param  [string] $access_secret_token [description]
	 * @return [string] [AppsecretProof]
	 */
	public function _getAppsecretProof($access_token)
	{
		$app_secret = Yii::$app->params['facebook']['app_secret'];
		return hash_hmac('sha256', $access_token, $app_secret); 
	}

	/**
	 * [_postCommentsSimpleQuery buidl a simple query post and their comments]
	 * @param  [string] $access_token_page [access_token_page by page]
	 * @return [string] $post_comments_query [query to call]
	 */
	private function _postCommentsSimpleQuery(){

		//$bussinessId = Yii::$app->params['facebook']['business_id'];
		$bussinessId = $this->_bussinesId;
		$end_date = strtotime(\app\helpers\DateHelper::add($this->end_date,'+1 day'));
		

		$post_comments_query = "{$bussinessId}/published_posts?fields=from,full_picture,icon,is_popular,message,attachments{unshimmed_url},shares,created_time,comments{from,created_time,is_hidden,like_count,message,permalink_url,parent,comment_count,attachment,comments.limit($this->_limit_commets){likes.limit(10),comments{message,permalink_url}}},updated_time&until={$end_date}&since={$this->start_date}&limit={$this->_limit_post}";

		return $post_comments_query;
	}

	/**
	 * [_getClient return client http request]
	 * @return [obj] [return object client]
	 */
	private function _getClient(){
		$this->_client = new Client(['baseUrl' => $this->_baseUrl]);
		return $this->_client;
	}


	function __construct(){
		
		// set resource 
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourceName);
		// get client
		$this->_getClient();
		
		parent::__construct(); 
	}
}

?>