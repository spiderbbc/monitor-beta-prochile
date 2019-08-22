<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

use app\models\file\JsonFile;
use app\models\Alerts;
use app\models\AlertsMencions;

use Abraham\TwitterOAuth\TwitterOAuth;
use Codebird\Codebird;
use Jenssegers\Date\Date;

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
	private $limit;
	private $params = [
		'lang' => 'es',
		'result_type' => 'recent',
		'count' => 100,
	//	'q' => '',
	//	'until' => '',
	//	'max_id' => '',

	];
	private $codebird;

	const FOLDERNAME = 'twitter';
	
	public function prepare($alert = []){
		if(!empty($alert)){
			$this->alertId    = $alert['id'];
			$this->start_date = $alert['config']['start_date'];
			$this->end_date   = $alert['config']['end_date'];
			/*$products_searched = $this->_getProductSearched($alert['products']);
			if($products_searched){
				//$alert['products'] = $products_searched;
				$products_params = $this->_setParamsbyProduct($products_searched);
			}*/
			$params = [];
			for($p = 0; $p < sizeOf($alert['products']); $p++){
				$this->params['q'] = $alert['products'][$p];

				$query  = $this->_getProductSearched($alert['products'][$p]);
				if($query){
					$date_searched = $this->_setDate($query['date_searched']);
					$this->params['until'] = $date_searched;
					$this->params['max_id'] = $query['max_id'];
				}else{
					$this->params['max_id'] = '';
					$date_searched = $this->_setDate($this->start_date);
					$this->params['until'] = $date_searched;
				}
				
				$params[] = $this->params;
			}

			var_dump($params);
		}
		return false;
	}	

	public function call($alert = []){
		
		$products = $alert['products'];

		//var_dump($products);
		
	}

	public function search_tweets($params = []){
		
		return null;
	}

	private function _getProductSearched($product){
		
		$products_to_searched = [];
		$query = (new \yii\db\Query())
		    ->select(['date_searched', 'max_id'])
		    ->from('alerts_mencions')
		    ->where([
		    	'alertId' => $this->alertId,
				'resourcesId' => $this->resourcesId,
				'condition' => AlertsMencions::CONDITION_ACTIVE,
				'type' => 'tweet',
				'term_searched' => $product,
		    ])
		    ->one();
		return $query;
	
	}

	private function _setParamsbyProduct($products_searched){

	}

	private function _setDate($date){
		$date_formateer = Yii::$app->formatter->asDatetime($date,'yyyy-MM-dd');;
		$date_obj = new Date($date_formateer);
		$date_change = $date_obj->add('1 day');
		$date = (array) $date_change;
		
		return explode(" ",$date['date'])[0];
	}


	function __construct($limit = 0){
		// set limit
		$this->limit = $limit;
		// set resource 
		$resourcesId = (new \yii\db\Query())
		    ->select('id')
		    ->from('resources')
		    ->where(['name' => ucfirst(self::FOLDERNAME)])
		    ->all();
		$this->resourcesId = ArrayHelper::getColumn($resourcesId,'id')[0];
		// get twitter login api
		$bearer_token = (new \yii\db\Query())
		    ->select('bearer_token')
		    ->from('credencials_api')
		    ->where(['resourceId' => $resourcesId])
		    ->all();
		Codebird::setBearerToken($bearer_token);    
		$this->codebird = Codebird::getInstance();  

		parent::__construct(); 
	}
}

?>