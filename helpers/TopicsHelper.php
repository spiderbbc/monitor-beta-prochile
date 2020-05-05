<?php
namespace app\helpers;

use yii;

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

	/**
	 * [getTopicsByResourceName get  topic related with resource name]
	 * @param  [string] $resourceName [name resource]
	 * @return [array]               [topics]
	 */
	public static function getTopicsByResourceName($resourceName)
	{
		$topics = \app\modules\topic\models\MTopics::find()->where(['status' => 1])->with(
			[
				'mTopicResources.resource' => function ($query) use($resourceName)
				{
					$query->andWhere(['name' => $resourceName]);
				}
			]
		)->asArray()->all();

		$results = [];

		for ($t=0; $t < sizeof($topics); $t++) { 
			if (\yii\helpers\ArrayHelper::keyExists('mTopicResources',$topics[$t])) {
				if (!is_null($topics[$t]['mTopicResources'])) {
					$mTopicResources = \yii\helpers\ArrayHelper::remove($topics[$t],'mTopicResources');
					for ($m=0; $m < sizeof($mTopicResources) ; $m++) { 
						if (!is_null($mTopicResources[$m]['resource'])) {
							$resource = \yii\helpers\ArrayHelper::remove($mTopicResources[$m],'resource');
							$topics[$t]['resource'] = $resource;
							$results[] = $topics[$t];
						}
					}// end loop for
				}// end if
			}
		}// end loop for


		return $results;
	}
	/**
	 * [saveOrUpdateWords save words in the table words: words are trending, term or hastag related in the searcg]
	 * @param  [type] $data    [description]
	 * @param  [type] $topicId [description]
	 * @return [type]          [description]
	 */
	public static function saveOrUpdateWords($data,$topicId)
	{
		foreach ($data as $key => $values) {
			for ($t=0; $t <sizeof($values) ; $t++) { 
				$is_word = \app\modules\topic\models\MWords::find()->where(
					[
						'topicId' => $topicId,
						'name' => $values[$t]['name'],
					]
				)->exists();
				if (!$is_word) {
					$model = new \app\modules\topic\models\MWords();
					$model->topicId = $topicId;
					$model->name = $values[$t]['name'];
					if ($model->save()) {
						$data[$key][$t]['wordId']= $model->id;
					}
				}else{

					$model = \app\modules\topic\models\MWords::find()->where(
						[
							'topicId' => $topicId,
							'name' => $values[$t]['name'],
						]
					)->one();
					$data[$key][$t]['wordId']= $model->id;

				}// end if !word
			}// end loop for
		}// end loop foreach
		return $data;
	}
	/**
	 * [saveOrUpdateTopicsStadistics save or update TopicsStadistics]
	 * @param  [type] $data       [description]
	 * @param  [type] $topicId    [description]
	 * @param  [type] $resourceId [description]
	 * @return [type]             [description]
	 */
	public static function saveOrUpdateTopicsStadistics($data,$topicId,$resourceId,$location=true)
	{
		foreach ($data as $key => $values) {
			for ($t=0; $t < sizeof($values) ; $t++) {
				$is_topic_stadistics = \app\modules\topic\models\MTopicsStadistics::find()->where(
					[
						'topicId' => $topicId,
						'resourceId' => $resourceId,
						'locationId' => ($location)? $key: null,
						'wordId' => $values[$t]['wordId'],
					]
				)->exists();
				if (!$is_topic_stadistics) {
					$model =  new \app\modules\topic\models\MTopicsStadistics();
					$model->topicId = $topicId;
					$model->resourceId = $resourceId;
					$model->locationId = ($location)? $key: null;
					$model->wordId = $values[$t]['wordId'];

					if($model->save()){
						$data[$key][$t]['topicStadisticId'] = $model->id;
					}else{
						var_dump($model->errors);
					}

				} else {
					$model = \app\modules\topic\models\MTopicsStadistics::find()->where(
						[
							'topicId' => $topicId,
							'resourceId' => $resourceId,
							'locationId' => ($location)? $key: null,
							'wordId' => $values[$t]['wordId'],
						]
					)->one();

					$data[$key][$t]['topicStadisticId'] = $model->id;
				}
				
			}
		}

		return $data;
	}
	/**
	 * [saveOrUpdateStadistics save or update Stadistics]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function saveOrUpdateStadistics($data)
	{
		foreach ($data as $key => $values) {
			for ($t=0; $t < sizeof($values); $t++) { 
				$is_stadistics = \app\modules\topic\models\MStatistics::find()->where(
					[
						'topicStaticId' => $values[$t]['topicStadisticId'],
						'timespan' => \app\helpers\DateHelper::getTodayDate()
					]
				)->exists();

				if (!$is_stadistics) {
					$model = new \app\modules\topic\models\MStatistics();
					$model->topicStaticId = $values[$t]['topicStadisticId'];
					$model->total = $values[$t]['total'];
					$model->timespan =\app\helpers\DateHelper::getTodayDate();

					if($model->save()){
						$data[$key][$t]['stadisticId'] = $model->id;
					}else{
						var_dump($model->errors);
					}


				} else {
					$model = \app\modules\topic\models\MStatistics::find()->where(
						[
							'topicStaticId' => $values[$t]['topicStadisticId'],
							'timespan' => \app\helpers\DateHelper::getTodayDate()
						]
					)->one();

					$model->total = $values[$t]['total'];

					if($model->save()){
						$data[$key][$t]['stadisticId'] = $model->id;
					}
				}
				
			}
		}

		return $data;
	}
	/**
	 * [saveOrUpdateAttachments save or update Attachments]
	 * @param  [type] $data [description]
	 * @return [type]       [description]
	 */
	public static function saveOrUpdateAttachments($data)
	{
		foreach ($data as $key => $values) {
			for ($v=0; $v < sizeof($values) ; $v++) { 
				$is_attachments = \app\modules\topic\models\MAttachments::find()->where(
					[
						'statisticId' => $values[$v]['stadisticId'],
						'src_url' => $values[$v]['url'],
					]
				)->exists();

				if (!$is_attachments) {
					$model = new \app\modules\topic\models\MAttachments();
					$model->statisticId = $values[$v]['stadisticId'];
					$model->src_url = $values[$v]['url'];
					$model->save();
				}
			}
		}
		return $data;
	}
	/**
	 * [getKeywordsDictionaries get words from dictionaries key = wordId value= word]
	 * @param  [type] $model [description]
	 * @return [type]        [description]
	 */
	public static function getKeywordsDictionaries($model)
	{
		$words = [];
		foreach ($model->mTopicsDictionaries as $mTopicDictionarie) {
            foreach ($mTopicDictionarie->dictionary->mKeywords as $kewords) {
                $words[$kewords->id] = \app\helpers\StringHelper::lowercase($kewords->name);
            }
        }
        return $words;
	}
}