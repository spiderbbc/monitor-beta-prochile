<?php 

namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\httpclient\Client;

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
	const TYPE_MENTIONS = 'web';


	public function prepare($alert)
	{
		if(!empty($alert)){
			
			$this->alertId        = $alert['id'];
			$this->start_date     = $alert['config']['start_date'];
			$this->end_date       = $alert['config']['end_date'];

			// validate there is one month old
			$today = \app\helpers\DateHelper::getToday();
			if (\app\helpers\DateHelper::diffInMonths($today,$this->start_date)) {
				return false;
			}

			
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
					'domains'   => $sources,
					'date_from' => $date_from,
					'date_to'   => $date_to,
					'page'      => 1
				];
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

		var_dump($this->data);
	}

	private function _getNews($params)
	{
		$data = [];
		$page = 1;

		$client = new Client();
		$params['apiKey'] = Yii::$app->params['newsApi']['apiKey'];

		//var_dump($params);
		$response = $client->createRequest()
				    ->setMethod('GET')
				    ->setUrl('http://newsapi.org/v2/everything')
				    ->setData($params)
				    ->send();

		if ($response->isOk) {
			if ($response->data['status'] == 'ok') {
				$data = $response->data['totalResults'];
			}
		    
		}
		return $data;
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
		if ($total >= 4) {
			$this->paginator = 4;
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