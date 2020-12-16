<?php
namespace app\models\api;

use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;


use app\models\file\JsonFile;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * BaseApi is the model behind the calls to models for API.
 *
 */
class BaseApi extends Model {

	public $alerts;
	public $products_count = 0;
	public $data;

	public $className = [
		'Twitter'                 => 'twitterApi',
		'Facebook Comments'       => 'facebookCommentsApi',
		'Facebook Messages'       => 'facebookMessagesApi',
		'Instagram Comments'      => 'InstagramCommentsApi',
		'Live Chat'               => 'liveChat',
		'Live Chat Conversations' => 'liveChatConversations',
		'Noticias Webs'           => 'newsApi',
		'Excel Document'          => 'excelDocument',
		'Paginas Webs'            => 'webPages',
	];

	/**
	 * [callResourcesApi call his method by the resources in the alert]
	 * @param  array  $alerts [group of array]
	 * @return [null]         
	 */
	public function callResourcesApi($alerts = []){

		if(!empty($alerts)){
			$resources = [];
			for($a = 0; $a < sizeOf($alerts); $a++){
				array_multisort(array_map('strlen', $alerts[$a]['config']['configSources']), $alerts[$a]['config']['configSources']);
				for($c = 0; $c < sizeOf($alerts[$a]['config']['configSources']); $c++){
					$name = $alerts[$a]['config']['configSources'][$c];
					if(ArrayHelper::keyExists($name,$this->className, false)){
						$className = $this->className[$name];
						$resources[$className][] = $alerts[$a]; 
					}
				}
			}

			foreach($resources as $method => $alerts){
				$this->{$method}($alerts);
			}
			
		} // if alert
	}
	/**
	 * [twitterApi call twitter model Api]
	 * @param  array  $alerts [alert with twitter resources]
	 * @return [null]        
	 */
	public function twitterApi($alerts = []){
		
		$products_count = $this->countAllTerms($alerts);
		$tweets = new \app\models\api\TwitterApi($products_count);

		foreach ($alerts as $alert) {
			$products_params = $tweets->prepare($alert);
			if($products_params){
				if($tweets->call($products_params)){
					// path to folder flat archives
					$tweets->saveJsonFile();
					
				}
			}
			
		}
	}
	/**
	 * [facebookCommentsApi call facebook comments model Api]
	 * @param  array  $alerts [alert with facebook resources]
	 * @return [null]        
	 */
	public function facebookCommentsApi($alerts = []){
		
		$facebookCommentsApi = new \app\models\api\FacebookCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $facebookCommentsApi->prepare($alert);
			if($query_params){
				$facebookCommentsApi->call($query_params);
				$facebookCommentsApi->saveJsonFile();
			}
		}

	}
	/**
	 * [facebookMessagesApi call facebook messages(inbox) model Api]
	 * @param  array  $alerts [alert with facebook resources]
	 * @return [null]        
	 */
	public function facebookMessagesApi($alerts = []){

		$facebookMessagesApi = new \app\models\api\FacebookMessagesApi();

		foreach ($alerts as $alert){
			$query_params = $facebookMessagesApi->prepare($alert);
			if($query_params){
				$facebookMessagesApi->call($query_params);
				$facebookMessagesApi->saveJsonFile();
			}
		}

	}
	/**
	 * [InstagramCommentsApi call instagram comments model Api]
	 * @param  array  $alerts [alert with instagram resources]
	 * @return [null]        
	 */
	public function InstagramCommentsApi($alerts = []){
		
		
		$InstagramCommentsApi = new \app\models\api\InstagramCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $InstagramCommentsApi->prepare($alert);
			if($query_params){
				$InstagramCommentsApi->call($query_params);
				$InstagramCommentsApi->saveJsonFile();
			}
		}

	}
	/**
	 * [liveChat call liveChat comments model Api]
	 * @param  array  $alerts [alert with liveChat resources]
	 * @return [null]        
	 */
	public function liveChat($alerts = []){
		$LivechatTicketApi = new \app\models\api\LiveTicketApi();

		foreach ($alerts as $alert){
			$query_params = $LivechatTicketApi->prepare($alert);
			
			if($query_params){
				$tickets = $LivechatTicketApi->call($query_params);
				$LivechatTicketApi->saveJsonFile($tickets);
			}
		}

		
	}
	/**
	 * [liveChatConversations call liveChat Conversations comments model Api]
	 * @param  array  $alerts [alert with liveChat resources]
	 * @return [null]        
	 */
	public function liveChatConversations($alerts = []){
		
		$LiveChatApi = new \app\models\api\LiveChatsApi();

		foreach ($alerts as $alert){
			$query_params = $LiveChatApi->prepare($alert);

			if($query_params){
				$chats = $LiveChatApi->call($query_params);
				$LiveChatApi->saveJsonFile($chats);
			}
		}
	}
	/**
	 * [excelDocument call excelDocument model Api]
	 * @param  array  $alerts [alert with excelDocument resources]
	 * @return [null]        
	 */
	public function excelDocument($alerts = []){
		//echo "excelDocument". "\n";
		
	}
	/**
	 * [newsApi call newsApi model Api]
	 * @param  array  $alerts [alert with newsApi resources]
	 * @return [null]        
	 */
	public function newsApi($alerts = []){

		$newsApi = new \app\models\api\NewsApi();
		$newsApi->setNumberCallsByAlert($alerts);
		foreach ($alerts as $alert){
			$query_params = $newsApi->prepare($alert);
			if($query_params){
				$news = $newsApi->call($query_params);
				$newsApi->saveJsonFile();
			}
		}
		
	}
	/**
	 * [webPages call webPages model Api]
	 * @param  array  $alerts [alert with webPages resources]
	 * @return [null]        
	 */
	public function webPages($alerts = [])
	{
		$scraping = new \app\models\api\Scraping();
		foreach ($alerts as $alert) {
			if ($alert['config']['urls'] != '') {
				$query_params = $scraping->prepare($alert);
				$crawlers = $scraping->getRequest();
				$content  = \app\helpers\ScrapingHelper::getContent($crawlers);
				$data     = \app\helpers\ScrapingHelper::setContent($content);
				$model    = $scraping->searchTermsInContent($data);
				if(!empty($model)){
					$scraping->saveJsonFile();
				}
			}
		}
	}
	/**
	 * [countAllTerms coutn term or products on alerts]
	 * @param  array  $alerts [alert with webPages resources]
	 * @return [int] count terms        
	 */
	public function countAllTerms($alerts = []){
		$count = 0;
		for($a = 0; $a < sizeOf($alerts); $a++){
			if(ArrayHelper::keyExists('products', $alerts[$a], false)){
				$count += count($alerts[$a]['products']);
			}
		}
		return $count;
	}

	/**
	 * [readDataResource call method read depende on the resource in the alert]
	 * @param  array  $alerts [group alerts]
	 * @return [null]         [description]
	 */
	public function readDataResource($alerts = []){
		$alerts= ArrayHelper::map($alerts,'id','config.configSources');
        $data = [];
        
 
        foreach($alerts as $alertid => $sources){
            foreach ($sources as $source){
                $jsonFile= new JsonFile($alertid,$source);
                if(!empty($jsonFile->findAll())){
                    $data[$alertid][$source] = $jsonFile->findAll();
                }
                    
            }
               
        }
        
 
        // no empty
        if(!empty($data)){
        	foreach ($data as $alertId => $resources){
        		foreach ($resources as $resource => $values){
        			$resourceName = str_replace(" ", "",ucwords($resource));
        			$this->{"readData{$resourceName}Api"}($alertId,$values);
        		}
        	}
        	
        }
       	//change the status if finish
		\app\helpers\AlertMentionsHelper::checkStatusAndFinishAlerts($alerts);

	}

	/**
	 * [readDataTwitterApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataTwitterApi($alertId,$data){
		$searchTwitterApi = new \app\models\search\TwitterSearch();
		$params = [$alertId,$data];
		if(!$searchTwitterApi->load($params)){
			// send email params in twitterApi no load with alertId and count($params)
		}
		if($searchTwitterApi->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Twitter');
		}
		
	}

	/**
	 * [readDataFacebookCommentsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataFacebookCommentsApi($alertId,$data){
		$searchFacebookApi = new \app\models\search\FacebookSearch();
		$searchFacebookApi->alertId = $alertId;

		$searchFacebookApi->load($data);

		if($searchFacebookApi->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Comments');

		}
	}
	/**
	 * [readDataFacebookMessagesApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataFacebookMessagesApi($alertId,$data){
		$searchFacebookMessagesApi = new \app\models\search\FacebookMessagesSearch();
		$searchFacebookMessagesApi->alertId = $alertId;

		$searchFacebookMessagesApi->load($data);
		if ($searchFacebookMessagesApi->search()) {
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Messages');
		}
		
		
		
	}
	/**
	 * [readDataInstagramCommentsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataInstagramCommentsApi($alertId,$data){
		$searchInstagramApi = new \app\models\search\InstagramSearch();
		$searchInstagramApi->alertId = $alertId;

		$searchInstagramApi->load($data);
		if($searchInstagramApi->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Instagram Comments');

		}
		
	}
	/**
	 * [readDataExcelDocumentApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataExcelDocumentApi($alertId,$data){
		$searchExcel = new \app\models\search\ExcelSearch();
		$params = [$alertId,$data];

		$searchExcel->load($params);

		if($searchExcel->search()){
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Excel Document');

		}

	}
	/**
	 * [readDataLiveChatApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataLiveChatApi($alertId,$data){ 
		$searchLiveApi = new \app\models\search\LiveTicketSearch(); 
		$params = [$alertId,$data]; 
 
		$searchLiveApi->load($params); 
		if($searchLiveApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat'); 
		} 
 
	} 
	/**
	 * [readDataliveChatConversationsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataliveChatConversationsApi($alertId,$data){ 
		$searchLiveChatApi = new \app\models\search\LiveChatSearch(); 
		$params = [$alertId,$data]; 
 
		$searchLiveChatApi->load($params); 
		if($searchLiveChatApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat Conversations'); 
		} 
 
	} 
	/**
	 * [readDataNoticiasWebsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataNoticiasWebsApi($alertId,$data){ 
		$searchNewsApi = new \app\models\search\NewsSearch();
		$params = [$alertId,$data];

		$searchNewsApi->load($params); 
		if($searchNewsApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Noticias Webs'); 
		} 
 
	} 
	/**
	 * [readDataPaginasWebsApi read and search depends on the params the alerts]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function readDataPaginasWebsApi($alertId,$data)
	{
		$searchScrapingApi = new \app\models\search\ScrapingSearch();
		$params = [$alertId,$data];

		$searchScrapingApi->load($params);
		if($searchScrapingApi->search()){ 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Paginas Webs'); 
		} 
	}
	/**
	 * [callInsights call api facebook graph to get insights]
	 * @param  [int] $alertId   [id of the alert]
	 * @param  [array] $data    [data from the json file]
	 * @return [null]          
	 */
	public function callInsights($userFacebook)
	{
	 	$insightsApi = new \app\models\api\InsightsApi();
	 	
	 	if ($insightsApi->prepare($userFacebook)) {

	 		$pageIns = $insightsApi->getInsightsPageInstagram();
	 		if ($pageIns) {
	 			$insightsApi->setInsightsPageInstagram($pageIns);
	 		}
	 		$insightsApi->setInsightsPostInstagram();
	 		$insightsApi->setStorysPostInstagram();

	 		$insightsApi->setInsightsPostonDbSave();

	 	}
	} 

	
}

?>