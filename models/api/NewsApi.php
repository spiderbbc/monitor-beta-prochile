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
	public $userId;
	public $alertId;
	public $end_date;
	public $start_date;
	public $resourcesId;
	public $products;
	
	public $data;

	public $total_call;
	public $paginator;

	const LIMIT_CALLS = 166;
	const NUMBER_DATA_BY_REQUEST = 20;
	const TYPE_MENTIONS = 'web';


	public function prepare($alert)
	{
		if(!empty($alert)){
		
			
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];

			// validate there is one month old
			/*$today = \app\helpers\DateHelper::getToday();
			if (\app\helpers\DateHelper::diffInMonths($today,$this->start_date)) {
				var_dump(\app\helpers\DateHelper::diffInMonths($today,$this->start_date));
				return false;
			}
*/
			
			// order products by his  length
			array_multisort(array_map('strlen', $alert['products']), $alert['products']);
			$this->products   = $alert['products'];
			// set paginator
			$this->_setPaginator();
			// set if search finish
			
			//$this->searchFinish();
			
			// set products
			$products_params = $this->setProductsParams();

			return $products_params;
		}
		return false;
	}
	/**
	 * [setProductsParams set params with the products to call at api]
	 */
	public function setProductsParams()
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
			$sources = implode(',', array_values(Yii::$app->params['newsApi']['targets']));
			

			if(!$productMention){
				
				$now = \app\helpers\DateHelper::getToday();
				$date_from = Yii::$app->formatter->asDate($this->start_date,'yyyy-MM-dd');
				$date_to = Yii::$app->formatter->asDate($this->end_date,'yyyy-MM-dd');
				
				if(\app\helpers\DateHelper::isToday($date_to)){
					$date_to = \app\helpers\DateHelper::add($this->end_date,'-1 day');
				}

				$productName  = urlencode($productName);


				$params[$this->products[$p]] = [
					'q'         => $productName,
					'qInTitle'  => $productName,
				//	'domains'   => $sources,
					'from' => $date_from,
					'to'   => $date_to,
					'sortBy' => 'publishedAt',
					'page'      => 1
				];
				// set apikey
				$params[$this->products[$p]]['apikey'] = Yii::$app->params['newsApi']['apiKey'];
			}

			
		}// end loop

		return $params;
	}

	public function call($products_params)
	{
		foreach($products_params as $productName => $params){
			//\yii\helpers\Console::stdout("loop in call method {$productName}.. \n", Console::BOLD);
			$this->data[$productName] =  $this->_getNews($params);
		}
		$this->_orderNews();
	}

	private function _getNews($params)
	{
		$data = [];
		$page = 0;
		$flag = true;

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
							$this->paginator = 1;
						}else{
							$total = round($totalResults / self::NUMBER_DATA_BY_REQUEST,0,PHP_ROUND_HALF_UP);
							$this->paginator = ($total > $this->paginator) ? $this->paginator : $total;
						}
						$flag = false;
					}
					// if is ok
					if ($response->data['status'] == 'ok') {
						$data[] = $response->data['articles'];

					}

				}else{
					break;
					// error happen send email
				}


			} else {
				break;
				// error happen send email
			}
			


			$params['page'] += 1;
			echo $this->paginator."\n";
			$this->paginator --;
		} while ($this->paginator > 0);
		return $data;
	}

	private function _orderNews()
	{
		$properties = [];
		$model = [];
		if (!empty($this->data)) {
			foreach ($this->data as $productName => $data) {
				if (!empty($this->data[$productName])) {
					
					$properties['term_searched'] = $productName;
					$properties['condition'] = 'ACTIVE';
					$properties['type'] = self::TYPE_MENTIONS;
					$this->_saveAlertsMencions($properties);

					for ($d=0; $d <sizeof($data) ; $d++) { 
						for ($i=0; $i <sizeof($data[$d]) ; $i++) { 
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

		$source = 'web';

		if(!empty($this->data)){
			$jsonfile = new JsonFile($this->alertId,$source);
			$jsonfile->load($this->data);
			$jsonfile->save();
		}

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
	/**
	 * [_setResourceId return the id from resource]
	 */
	private function _setResourceId(){
		
		$socialId = (new \yii\db\Query())
		    ->select('id')
		    ->from('type_resources')
		    ->where(['name' => 'Web'])
		    ->one();
		
		
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => 'Web page','resourcesId' => $socialId['id']])
		    ->all();
		

		$this->resourcesId = yii\helpers\ArrayHelper::getColumn($resourcesId,'id')[0];    
	}
	
	function __construct(){
		// set resource 
		$this->_setResourceId();
		
		parent::__construct(); 
	}
}



?>