<?php 

namespace app\models\api;

use Yii;
use yii\base\Model;

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