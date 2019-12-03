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
		'Web page'                => 'webpage',
		'Excel Document'          => 'excelDocument',
	];


	public function callResourcesApi($alerts = []){
		Console::stdout("Running: ".__METHOD__."\n", Console::BOLD);

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

	public function twitterApi($alerts = []){
		
		Console::stdout("calling twitter api class\n", Console::BOLD);
		$products_count = $this->countAllTerms($alerts);
		$tweets = new \app\models\api\TwitterApi($products_count);

		foreach ($alerts as $alert) {
			$products_params = $tweets->prepare($alert);
			if($products_params){
				$data = $tweets->call($products_params);
				if(!empty($data)){
					// path to folder flat archives
					$folderpath = [
						'source' => 'Twitter',
						'documentId' => $alert['id'],
					];
					$this->saveJsonFile($folderpath,$data);
				}
			}
			
		}
		echo "twitterApi". "\n";
	}

	public function facebookCommentsApi($alerts = []){
		
		Console::stdout("calling facebookCommentsApi api class\n ", Console::BOLD);
		
		$facebookCommentsApi = new \app\models\api\FacebookCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $facebookCommentsApi->prepare($alert);
			if($query_params){
				$facebookCommentsApi->call($query_params);
				$facebookCommentsApi->saveJsonFile();
			}
		}

	}

	public function facebookMessagesApi($alerts = []){

		Console::stdout("calling facebookMessagesApi api class\n ", Console::BOLD);
		
		$facebookMessagesApi = new \app\models\api\FacebookMessagesApi();

		foreach ($alerts as $alert){
			$query_params = $facebookMessagesApi->prepare($alert);
			if($query_params){
				$facebookMessagesApi->call($query_params);
				$facebookMessagesApi->saveJsonFile();
			}
		}

	}

	public function InstagramCommentsApi($alerts = []){
		
		Console::stdout("calling InstagramCommentsApi api class\n ", Console::BOLD);
		
		$InstagramCommentsApi = new \app\models\api\InstagramCommentsApi();

		foreach ($alerts as $alert){
			$query_params = $InstagramCommentsApi->prepare($alert);
			if($query_params){
				$InstagramCommentsApi->call($query_params);
				$InstagramCommentsApi->saveJsonFile();
			}
		}

	}

	public function liveChat($alerts = []){
		echo "liveChat". "\n";
		$LivechatTicketApi = new \app\models\api\LiveTicketApi();

		foreach ($alerts as $alert){
			$query_params = $LivechatTicketApi->prepare($alert);
			
			if($query_params){
				$tickets = $LivechatTicketApi->call($query_params);
				$LivechatTicketApi->saveJsonFile($tickets);
			}
		}

		
	}

	public function liveChatConversations($alerts = []){
		echo "liveChat Conversation". "\n";
		$LiveChatApi = new \app\models\api\LiveChatsApi();

		foreach ($alerts as $alert){
			$query_params = $LiveChatApi->prepare($alert);

			if($query_params){
				$chats = $LiveChatApi->call($query_params);
				$LiveChatApi->saveJsonFile($chats);
			}
		}
	}

	public function webpage($alerts = []){
		echo "webpage". "\n";
	}

	public function countAllTerms($alerts = []){
		$count = 0;
		for($a = 0; $a < sizeOf($alerts); $a++){
			if(ArrayHelper::keyExists('products', $alerts[$a], false)){
				$count += count($alerts[$a]['products']);
			}
		}
		return $count;
	}


	public function saveJsonFile($folderpath = [],$data){

		if(!empty($data)){
			// pass to variable
		    list('source' => $source,'documentId' => $documentId) = $folderpath;
			// call jsonfile
			$jsonfile = new JsonFile($documentId,$source);
			$jsonfile->load($data);
			$jsonfile->save();
		}

	}

	public function readDataResource($alerts = []){
		$alerts= ArrayHelper::map($alerts,'id','config.configSources');
        $data = [];
        foreach($alerts as $alertid => $sources){
            foreach ($sources as $source){
                $jsonFile= new JsonFile($alertid,$source);
                if(!empty($jsonFile->findAll())){
                    $data[$alertid][$source] = $jsonFile->findAll();
                    //\app\helpers\DocumentHelper::moveFilesToProcessed($alertid,$source);
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
       

	}


	public function readDataTwitterApi($alertId,$data){
		echo "calling readDataTwitterApi \n";
		$searchTwitterApi = new \app\models\search\TwitterSearch();
		$params = [$alertId,$data];
		if(!$searchTwitterApi->load($params)){
			// send email params in twitterApi no load with alertId and count($params)
		}
		if($searchTwitterApi->search()){
			echo "moved file";
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Twitter');
		}
		
	}


	public function readDataFacebookCommentsApi($alertId,$data){
		echo "calling readDataFacebookCommentsApi \n";
		$searchFacebookApi = new \app\models\search\FacebookSearch();
		$params = [$alertId,$data];

		$searchFacebookApi->load($params);

		if($searchFacebookApi->search()){
			echo "moved file";
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Comments');

		}
	}

	public function readDataFacebookMessagesApi($alertId,$data){
		echo "calling readDataFacebookMessagesApi \n";
		$searchFacebookMessagesApi = new \app\models\search\FacebookMessagesSearch();
		$params = [$alertId,$data];

		$searchFacebookMessagesApi->load($params);
		$searchFacebookMessagesApi->search();
		\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Facebook Messages');
		
	}

	public function readDataInstagramCommentsApi($alertId,$data){
		echo "calling readDataInstagramCommentsApi \n";
		$searchInstagramApi = new \app\models\search\InstagramSearch();
		$params = [$alertId,$data];

		$searchInstagramApi->load($params);


		if($searchInstagramApi->search()){
			echo "moved file";
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Instagram Comments');

		}
		
	}

	public function readDataExcelDocumentApi($alertId,$data){
		echo "calling readDataExcelDocumentApi \n";
		$searchExcel = new \app\models\search\ExcelSearch();
		$params = [$alertId,$data];

		$searchExcel->load($params);

		if($searchExcel->search()){
			echo "moved file";
			//\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Excel Document');

		}

	}

	public function readDataLiveChatApi($alertId,$data){ 
		echo "calling readDataLiveChatApi \n"; 
		$searchLiveApi = new \app\models\search\LiveTicketSearch(); 
		$params = [$alertId,$data]; 
 
		$searchLiveApi->load($params); 
		if($searchLiveApi->search()){ 
			echo "moved file"; 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat'); 
		} 
 
	} 

	public function readDataliveChatConversationsApi($alertId,$data){ 
		echo "calling readDataLiveChatApi \n"; 
		$searchLiveChatApi = new \app\models\search\LiveChatSearch(); 
		$params = [$alertId,$data]; 
 
		$searchLiveChatApi->load($params); 
		if($searchLiveChatApi->search()){ 
			echo "moved file"; 
			\app\helpers\DocumentHelper::moveFilesToProcessed($alertId,'Live Chat Conversations'); 
		} 
 
	} 

	
}

?>