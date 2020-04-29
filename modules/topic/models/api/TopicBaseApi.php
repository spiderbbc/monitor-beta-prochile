<?php
namespace app\modules\topic\models\api;


/**
 * 
 */
class TopicBaseApi
{
	/**
	 * [callResourcesApiTopic call function if exist in the class]
	 * @param  [type] $topics [description]
	 * @return [type]         [description]
	 */
	public function callResourcesApiTopic($topics){
		
		$resources = [];
		for ($t=0; $t <sizeof($topics) ; $t++) { 
			if (!empty($topics[$t]['resource'])) {
				$resourceName = $topics[$t]['resource']['name'];
				if (method_exists($this, $resourceName.'Api')) {
					$method = "${resourceName}Api";
					$resources[$method][] = $topics[$t];
				}
			}
		}

		foreach($resources as $method => $topics){
			$this->{$method}($topics);
		}
	}
	/**
	 * [TwitterApi functions call to Twitter Api ]
	 * @param array $topics [description]
	 */
	public function TwitterApi($topics = []){
		$trending = new \app\modules\topic\models\api\TwitterApi();
		foreach ($topics as $topic) {
			if ($trending->prepare($topic)) {
				$trends = $trending->callTrendings();
				if (!empty($trends)) {
					$trending->saveData();
				}
			}
		}
	}	
}

?>