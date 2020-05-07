<?php

namespace app\modules\topic\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;


use \app\modules\topic\models\MTopics;
use \app\modules\topic\models\MTopicsStadistics;
use \app\modules\topic\models\MStatistics;

/**
 * 
 */
class TopicController extends Controller
{
	/**
	 * [behaviors negotiator to return the response in json format]
	 * @return [array] [for controller]
	 */
	public function behaviors(){
	   return [
	        [
	            'class' => 'yii\filters\ContentNegotiator',
	            'only' => [
	            	'numbers-resources',
	            	'cloud-word',
	            	'cloud-dictionaries',
	            	'stadistics-date',
	            ],  // in a controller
	            // if in a module, use the following IDs for user actions
	            // 'only' => ['user/view', 'user/index']
	            'formats' => [
	                'application/json' => Response::FORMAT_JSON,
	            ],
	            'languages' => [
	                'en',
	                'de',
	            ],
	        ],
	   ];
	}

	/**
	 * [actionNumbersResources returns the number of entities with topic_stadistic]
	 * @param  [type] $topicId    [description]
	 * @return [array] []
	 */
	public function actionNumbersResources($topicId)
	{
		$topic = MTopics::find()->where(['id' => $topicId])->with([
			'sources'=> function ($query) use ($topicId)
			{
				$query->select(['id','name'])->with([
					'mTopicsStadistics' => function ($query) use ($topicId)
					{
						$query->andWhere(['topicId' => $topicId]);
					}
				]);
			},
		])->asArray()->one();
				
        return [
        	'topic' => $topic
        ];
	}
	/**
	 * [actionCloudWord return array words from topics]
	 * @param  [type] $topicId    [description]
	 * @param  [type] $resourceId [description]
	 * @return [type]             [description]
	 */
	public function actionCloudWord($topicId,$resourceId)
	{
		$model = MTopicsStadistics::find()->where(
			[
				'topicId' => $topicId,
				'resourceId' => $resourceId,
			]
		)->with(['word','mStatistics','mAttachments'])->asArray()->all();

		$words = [];

		if (!is_null($model)) {
			for ($i=0; $i < sizeof($model); $i++) { 
				$words[] = [
					'text' => $model[$i]['word']['name'],
					'weight' => $model[$i]['mStatistics'][0]['total'],
					'link' => $model[$i]['mAttachments']['src_url'],
				];
			}
		}

		return [
        	'words' => $words
        ];
	}

	/**
	 * [actionCloudDictionaries return array the words dictionaries for topic]
	 * @param  [int] $topicId [description]
	 * @return [array]          [description]
	 */
	public function actionCloudDictionaries($topicId)
	{
		$model = $this->findModel($topicId);
		$mTopicsDictionaries = [];
		if ($model->mTopicsDictionaries) {
			foreach ($model->mTopicsStadistics as $topicsStadistic) {
				foreach ($topicsStadistic->mStatistics as $stadistic) {
					if ($stadistic->mWordsDictionaryStatistics) {
						//$tmp = [];
						foreach ($stadistic->mWordsDictionaryStatistics as $dictionaryStatistics) {
							if (!isset($mTopicsDictionaries[$dictionaryStatistics->keyword->name])) {
								$mTopicsDictionaries[$dictionaryStatistics->keyword->name] = $dictionaryStatistics->count;
							}else{
								$mTopicsDictionaries[$dictionaryStatistics->keyword->name] += $dictionaryStatistics->count;
							}
							
						}
					}
				}
			}
		}
		$wordsStadistic = [];
		if (!empty($mTopicsDictionaries)) {
			foreach ($mTopicsDictionaries as $word => $total) {
				$wordsStadistic[] = [
					'text' => $word,
					'weight' =>  \Yii::$app->formatter->asDecimal($total),
				];
			}
		}

		return [
			'model' => $wordsStadistic
		];
	}
	
	public function actionStadisticsDate($topicId)
	{
		$model = $this->findModel($topicId);

		$expression = new Expression("DATE(FROM_UNIXTIME(timespan)) AS date,total");
		$expressionGroup = new Expression("DATE(FROM_UNIXTIME(timespan))");
		
		$mStadistics = [];
		$mWord = [];
		$seriesWords = [];

		$end_date = \app\helpers\DateHelper::add($model->end_date,'+2 days');
		$period = \app\helpers\DateHelper::periodDates($model->createdAt,$end_date);

		if ($model->mTopicsStadistics) {
			foreach ($model->mTopicsStadistics as $topicsStadistic) {

				$rows = (new \yii\db\Query())
			          ->select($expression)
			          ->from('m_statistics')
			          ->where(['topicStaticId' => $topicsStadistic->id])
			          ->groupBy($expressionGroup)
			          ->all();

				$mStadistics[] = [
					'name' => $topicsStadistic->word->name,
					'data' => $rows
				];
			}// end loop mTopicsStadistics
		}// end if

		// reoorder data
		$seriesWords = \app\helpers\TopicsHelper::orderSeries($mStadistics,$period);
		

		return [
			'seriesWords' => $seriesWords,
			'period' => $period,
			//'mStadistics' => $mStadistics,
		];
	}


	/**
     * Finds the MTopics model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return MTopics the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = MTopics::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(\Yii::t('app', 'The requested page does not exist.'));
    }
}
