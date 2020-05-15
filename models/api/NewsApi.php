<?php 

namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;
use app\models\file\JsonFile;
use app\models\AlertsMencions;

/**
 * class wrapper the calls to newsapi.org/
 */
class NewsApi extends Model
{
	public $alertId;
	public $start_date;
	public $end_date;
	public $products;
	public $resourcesId;
	
	public $data;

	public $total_call;
	public $paginator;
	
	private $lang = ['es','en'];
	private $resourceName = 'Noticias Webs';

	public $status_history_search = 'Pending';
	public $condition_alert_mention = 'ACTIVE';

	const LIMIT_CALLS = 166;
	const TYPE_MENTIONS = 'news';
	const NUMBER_DATA_BY_REQUEST = 20;


	public function prepare($alert)
	{
		if(!empty($alert)){
			
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];
			// reset data
			$this->data = [];

			/**
			 * validate there is one month old
			 */
			
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->products   = $alert['products'];
			// set paginator
			$this->_setPaginator();
			// set products and his languaje search
			$lang = $this->lang[$alert['config']['lang']];
			$products_params = $this->setProductsParams($lang);
			if (empty($products_params)) {
				$this->changeStatusAlertMentions();
			}else{
				return $products_params;
			}
			
		}
		return false;
	}
	/**
	 * [setProductsParams set params with the products to call at api]
	 */
	public function setProductsParams($lang)
	{
		// if there old call and number products is higher than number calls
		if (count($this->products) > $this->total_call){
			if(\app\models\AlertsMencions::find()->where(['alertId' => $this->alertId,'type' => self::TYPE_MENTIONS,'resourcesId' => $this->$this->resourcesId,'condiction' => 'ACTIVE'])->exists()){
				// return only products than no have search
				return null;
			}
		}

		$params = [];
		for ($p=0; $p < sizeof($this->products) ; $p++) { 
			
			$productName = $this->products[$p];
			$productMention = $this->_getAlertsMencionsByProduct($productName);
			// sources 
			$domains = implode(',', array_values(Yii::$app->params['newsApi']['targets']));

			if (!$productMention) {
				# start date and end date is today
				if (\app\helpers\DateHelper::isToday((int)$this->start_date) && \app\helpers\DateHelper::isToday((int)$this->end_date)) {
					$from = $to = \app\helpers\DateHelper::getToday();
				}else{
					// is between
					$today =  Yii::$app->formatter->asDatetime(\app\helpers\DateHelper::getToday(),'yyyy-MM-dd');
					if (\app\helpers\DateHelper::isBetweenDate($today,$this->start_date,$this->end_date)) {
						$from = $this->start_date;
						$to = (string) \app\helpers\DateHelper::getToday();
					}else{
						$from = $this->start_date;
						$to = $this->end_date;
						$this->condition_alert_mention = 'INACTIVE';
						$this->status_history_search = 'Finish';
					}// end between

				}// end if is today
			} else {
				$date_searched_flag   = strtotime(\app\helpers\DateHelper::add($this->end_date,'1 day'));
				if ($productMention->date_searched < $date_searched_flag) {
					$from = $productMention->date_searched;
					$to = $productMention->date_searched;
				}else{
					$this->condition_alert_mention = 'INACTIVE';
					$this->status_history_search = 'Finish';
					continue;
				}
			}
			
			

			$params[$this->products[$p]] = [
				'q' => urlencode($this->products[$p]),
				'qInTitle' => urlencode($this->products[$p]),
				'from' => Yii::$app->formatter->asDatetime($from,'yyyy-MM-dd'),
				'to' => Yii::$app->formatter->asDatetime($to,'yyyy-MM-dd'),
				'language' => $lang,
				'sortBy' => 'relevancy',
				'page' => 1,
				'apikey' => Yii::$app->params['newsApi']['apiKey'],
				'domains' =>  $domains,
				
			];
		}// end loop
		
		return $params;
	}
	/**
	 * [call to api for each products or word]
	 * @param  [type] $products_params [params to call api]
	 * @return [type]                  [null]
	 */
	public function call($products_params)
	{
		foreach($products_params as $productName => $params){
			$this->data[$productName] =  $this->_getNews($params);
		}
		
		$this->_orderNews();
		// search finish
		$this->searchFinish(); 
		
	}
	/**
	 * [_getNews call to api]
	 * @param  [type] $params [params to call api]
	 * @return [type]         [array data]
	 */
	private function _getNews($params)
	{
		$data = [];
		$page = 0;
		$flag = true;
		$paginator = $this->paginator;
		//$paginator = 1;

		$client = new Client();

		do {
			
			$response = $client->createRequest()
				->setMethod('GET')
				->setUrl('http://newsapi.org/v2/everything')
				->setData($params)
				->send();

			if ($response->isOk) {
				
				if ($response->data['status'] == 'ok') {
					// set paginator in based result
					if ($flag) {
						$totalResults = $response->data['totalResults'];
						if ($totalResults < self::NUMBER_DATA_BY_REQUEST) {
							$paginator = 1;
						}else{
							$total = round($totalResults / self::NUMBER_DATA_BY_REQUEST,0,PHP_ROUND_HALF_UP);
							$paginator = ($total > $paginator) ? $paginator : $total;
							
							/*echo "---------------------\n";
							echo " totalResults: ".$totalResults."\n";
							echo " total: ".$total."\n";
							echo " paginator: ".$paginator."\n";
							echo "---------------------\n";*/
						}
						$flag = false;
					}
					// if is ok
					if ($response->data['status'] == 'ok') {
						$data[] = $response->data['articles'];

					}

				}else{
					var_dump($response->data['status']);
					break;
					// error happen send email
				}


			} else {
				var_dump($response);
				break;
				// error happen send email
			}
			


			$params['page'] += 1;
			$paginator --;
		} while ($paginator > 0);
		return $data;
	}
	/**
	 * [_orderNews order array data to save in method jsonsave]
	 * @return [type] [null]
	 */
	private function _orderNews()
	{
		$properties = [];
		$model = [];
		// check if save date searched
		if ($this->status_history_search == 'Pending' && $this->condition_alert_mention == 'ACTIVE') {
			$today = \app\helpers\DateHelper::getToday();
			//$date_searched = $today;
			$date_searched = \app\helpers\DateHelper::addHours($today,Yii::$app->params['newsApi']['time_hours_sleep']);
		}

		if (!empty($this->data)) {
			foreach ($this->data as $productName => $data) {
				if (!empty($this->data[$productName])) {
					
					$properties['term_searched'] = $productName;
					$properties['condition'] = $this->condition_alert_mention;
					$properties['type'] = self::TYPE_MENTIONS;
					$properties['date_searched'] = (isset($date_searched)) ? $date_searched : null;

					$this->_saveAlertsMencions($properties);

					for ($d=0; $d <sizeof($data) ; $d++) { 
						for ($i=0; $i <sizeof($data[$d]) ; $i++) { 
							$content = (isset($data[$d][$i]['content'])) ? $data[$d][$i]['content'] : null;
							if(!is_null($content)){
								$content = \app\helpers\StringHelper::collapseWhitespace($content);
							}
							$data[$d][$i]['message_markup'] = $content;
							$model[$productName][] = $data[$d][$i];
						}
					}
				}
			}
		}

		$this->data = $model;
	}
	/**
	 * [_saveAlertsMencions save in alerts_mencions model]
	 * @param  array  $properties [description]
	 * @return [type]             [description]
	 */
	private function _saveAlertsMencions($properties = []){
		
		$model =  AlertsMencions::find()->where([
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId,
			'type'          => self::TYPE_MENTIONS,
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
	 * [saveJsonFile save a json file]
	 * @return [none] [description]
	 */
	public function saveJsonFile(){

		if(!empty($this->data)){
			$jsonfile = new JsonFile($this->alertId,$this->resourceName);
			$jsonfile->load($this->data);
			$jsonfile->save();
		}

	}
	/**
	 * [searchFinish save his status in HistorySearch]
	 * @return [type] [description]
	 */
	private function searchFinish()
	{

		$model = [
            'Noticias Webs' => [
                'resourceId' => $this->resourcesId,
                'status' => $this->status_history_search
            ]
        ];
		
		\app\helpers\HistorySearchHelper::createOrUpdate($this->alertId, $model);

	}
	/**
	 * [_getAlertsMencionsByProduct get model by product name]
	 * @param  [type] $productName [name product]
	 * @return [obj / boolean]     [a model if exits or false if not]
	 */
	private function _getAlertsMencionsByProduct($productName){

		$where = [
			'type'          => self::TYPE_MENTIONS,
			'term_searched' => $productName,
			'alertId'       => $this->alertId,
			'resourcesId'   => $this->resourcesId
		];

		$is_exits = \app\models\AlertsMencions::find()->where($where)->exists();
		
		$model = false;
		
		if($is_exits){
			$model = \app\models\AlertsMencions::find()->where($where)->one();
		}

		return $model;
	
	}

	private function changeStatusAlertMentions()
	{
		// check if save date searched
		if ($this->status_history_search == 'Finish' && $this->condition_alert_mention == 'INACTIVE') {
			$alert = \app\models\Alerts::findOne($this->alertId);
			$alert->status = 0;
			$alert->save();
			$alerts_mencions = \app\models\AlertsMencions::find()->where(['alertId' => $this->alertId])->all();
			foreach ($alerts_mencions as $alerts_mencion) {
				$alerts_mencion->condition = $this->condition_alert_mention;
				$alerts_mencion->save();
			}

		}
	}

	/**
	 * [setNumberCallsByAlert set number the call by alert by dividing 
	 * LIMIT_CALLS / total alerts (LIMIT_CALLS = 500 calls that allows api / 3 calls a day) ]
	 * @param [type] $alerts [description]
	 */
	public function setNumberCallsByAlert($alerts)
	{
		$alerts_count = count($alerts);

		$total_call = self::LIMIT_CALLS / $alerts_count;
		$this->total_call = round($total_call, 0, PHP_ROUND_HALF_DOWN);
		
	}
	/**
	 * [_setPaginator set number pagination]
	 * @param [type] $total_products [description]
	 */
	private function _setPaginator()
	{
		$total = $this->total_call / count($this->products);
		if ($total >= 5) {
			$this->paginator = 5;
		}else{
			$this->paginator = $total;	
		}
		
	}
	
	function __construct(){
		// set resource 
		$this->resourcesId = \app\helpers\AlertMentionsHelper::getResourceIdByName($this->resourceName);
		
		parent::__construct(); 
	}
}



?>