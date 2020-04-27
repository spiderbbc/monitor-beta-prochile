<?php
namespace app\helpers;

use yii;
use yii\db\Expression;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * TopicsHelper wrapper for tables function.
 *
 */
class TopicsHelper
{

	/**
	 * [saveOrUpdateResourceId create or update MTopicResources models]
	 * @param  [type] $resourcesId [description]
	 * @param  [type] $topicId     [description]
	 * @return [type]              [description]
	 */
	public static function saveOrUpdateResourceId($resourcesId,$topicId)
	{
		$istopicResource = \app\modules\topic\models\MTopicResources::find()->where(['topicId' => $topicId])->exists();
		if ($istopicResource) {
			\app\modules\topic\models\MTopicResources::deleteAll('topicId ='.$topicId);
		}
		for ($r=0; $r < sizeof($resourcesId) ; $r++) { 
			$model = new \app\modules\topic\models\MTopicResources();
			$model->topicId = $topicId;
			$model->resourceId = $resourcesId[$r];
			$model->save();
		}
	}
	/**
	 * [saveOrUpdateLocationId create or update MTopicsLocation models]
	 * @param  [type] $locationsId [description]
	 * @param  [type] $topicId     [description]
	 * @return [type]              [description]
	 */
	public static function saveOrUpdateLocationId($locationsId,$topicId)
	{
		$istopicResource = \app\modules\topic\models\MTopicsLocation::find()->where(['topicId' => $topicId])->exists();
		if ($istopicResource) {
			\app\modules\topic\models\MTopicsLocation::deleteAll('topicId ='.$topicId);
		}
		for ($l=0; $l < sizeof($locationsId) ; $l++) { 
			$model = new \app\modules\topic\models\MTopicsLocation();
			$model->topicId = $topicId;
			$model->locationId = $locationsId[$l];
			$model->save();
		}
	}

	/**
	 * [saveOrUpdateDictionaries create or update MDictionaries models]
	 * @param  array  $dictionariesProperty [description]
	 * @return [type]                       [description]
	 */
	public static function saveOrUpdateDictionaries($dictionariesProperty = [])
	{
		$dictionaries = [];
		if (!empty($dictionariesProperty)) {
			for ($d=0; $d < sizeof($dictionariesProperty) ; $d++) { 
				$id = $dictionariesProperty[$d]['id'];
				$model = \app\modules\topic\models\MDictionaries::findOne($id);
				if ($model) {
					$model->name = $dictionariesProperty[$d]['name'];
				}else{
					$model = new \app\modules\topic\models\MDictionaries;
					$model->id = $dictionariesProperty[$d]['id'];
					$model->name = $dictionariesProperty[$d]['name'];
				}
				if ($model->save()) {
					$dictionaries[$model->id] = $model->name;
				}
			}
		}
		return $dictionaries;
	}
	/**
	 * [saveOrUpdateDictionariesWords create or update MDictionaries models]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateDictionariesWords($content)
	{
		if (!empty($content)) {
			foreach ($content as $dictionaryName => $words) {
				$dictionary = \app\modules\topic\models\MDictionaries::findOne(['name' => $dictionaryName]);
				if ($dictionary) {
					for ($w=0; $w < sizeof($words) ; $w++) {
						$keyword = $words[$w]; 
						$model = \app\modules\topic\models\MKeywords::findOne(
							['dictionaryId' => $dictionary->id,'name' => $keyword]
						);
						if (is_null($model)) {
							$model = new \app\modules\topic\models\MKeywords;
							$model->dictionaryId = $dictionary->id;
							$model->name = $keyword;
							if (!$model->save()) {
								var_dump($model->errors);
								die();
							}
						}
					}
				}else{
					throw new Exception("Error Processing Request: not find dictionary", 1);
					
				}
			}
		}
	}
	/**
	 * [saveOrUpdateTopicsDictionaries create or update MTopicsDictionary models]
	 * @param  array  $sheetIds [description]
	 * @param  [type] $topicId  [description]
	 * @return [type]           [description]
	 */
	public static function saveOrUpdateTopicsDictionaries($sheetIds = [],$topicId)
	{
		if (!empty($sheetIds)) {
			\app\modules\topic\models\MTopicsDictionary::deleteAll('topicId ='.$topicId);
			foreach ($sheetIds as $sheetId) {
				$istopicDictionary = \app\modules\topic\models\MTopicsDictionary::find()->where(
					['topicId' => $topicId,'dictionaryID' => $sheetId]
				)->exists();
				if (!$istopicDictionary) {
					$model = new  \app\modules\topic\models\MTopicsDictionary();
					$model->topicId = $topicId;
					$model->dictionaryID = $sheetId;
					$model->save();
				}
			}
		}
	}
	/**
	 * [saveOrUpdateUrls create or update MUrlsTopics models]
	 * @param  [type] $urls    [description]
	 * @param  [type] $topicId [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateUrls($urls,$topicId)
	{
		$istopicUrls = \app\modules\topic\models\MUrlsTopics::find()->where(['topicId' => $topicId])->exists();
		if ($istopicUrls) {
			\app\modules\topic\models\MUrlsTopics::deleteAll('topicId ='.$topicId);
		}
		for ($u=0; $u < sizeof($urls) ; $u++) { 
			$model = new \app\modules\topic\models\MUrlsTopics();
			$model->topicId = $topicId;
			$model->url = $urls[$u];
			$model->save();
		}
	}
}