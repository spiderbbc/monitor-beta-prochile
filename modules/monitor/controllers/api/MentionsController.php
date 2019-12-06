<?php

namespace app\modules\monitor\controllers\api;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class MentionsController extends \yii\web\Controller
{

    public function actionIndex($alertId)
    {
       \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
       
		
       
       /*
		
       

       

       
       */

       // menciones por fecha

       /*$expression = new Expression("DATE(FROM_UNIXTIME(created_time)) AS date,COUNT(*) AS total");
       $expressionGroup = new Expression(" DATE(FROM_UNIXTIME(created_time))");

       $rows = (new \yii\db\Query())
        ->select($expression)
        ->from('mentions')
        ->groupBy($expressionGroup)
        ->all();

        var_dump($rows);*/

        // menciones por recurso y fecha
        
        /*$expression = new Expression("DATE(FROM_UNIXTIME(created_time)) AS date,COUNT(*) AS total");
        $expressionGroup = new Expression("DATE(FROM_UNIXTIME(created_time))");
        
        $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
        $resourceDateCount = [];
       	
       	foreach ($alertMentions as $alertMention){
       		if($alertMention->mentionsCount){
       			if(!in_array($alertMention->resources,$resourceDateCount)){
       				$rows = (new \yii\db\Query())
			        ->select($expression)
			        ->from('mentions')
			        ->where(['alert_mentionId' => $alertMention->id])
			        ->groupBy($expressionGroup)
			        ->all();

			        foreach ($rows as $row){
			        	$resourceDateCount[$alertMention->resources->name][] = $row;	
			        }

       				
       			} // end if in_array

       		}// is not null 
       		
       	}// end foreach
       	echo "<pre>";
       	print_r($resourceDateCount);
       	echo "</pre>";*/

       	// menciones por producto por recurso y por fecha
       	

    }


  public function actionCountMentions($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    // cuenta si la alerta tiene menciones
    $rows = (new \yii\db\Query())
    ->from('alerts_mencions')
    ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
    ->where(['alertId' => $alertId])
    ->count();
      
    return array('status'=>true,'count'=>$rows);

  }

  public function actionCountSourcesMentions($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    // cuenta por menciones
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $resourceCount = [];
    foreach ($alertMentions as $alertMention){
      $mentionCount = \app\models\Mentions::find()->where(['alert_mentionId' => $alertMention->id])->count(); 
      $resourceCount[$alertMention->resources->name][] = $mentionCount;
    }
    $resourceCount = array_map("array_sum", $resourceCount);
     
    return array('status'=>true,'resources'=>$resourceCount);

  }

  public function actionCountByProducts($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    // cuenta por resource and producto
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $resourceCount = [];
    foreach ($alertMentions as $alertMention){
      $mentionCount = \app\models\Mentions::find()->where(['alert_mentionId' => $alertMention->id])->count(); 
      if($mentionCount){
        $resourceCount[$alertMention->resources->name][$alertMention->term_searched][] = $mentionCount;
      }
    }
    return array('status'=>true,'resources'=>$resourceCount);


  }

  public function actionListMentions($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

    // list mentions: resource - products - author - mentions
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $alertsId = [];
    foreach ($alertMentions as $alertMention){
      if($alertMention->mentionsCount){
        $alertsId[] = $alertMention->id;
      }
    }

    $mentions = \app\models\Mentions::find()->where(['alert_mentionId' => $alertsId])->with(['alertMention','alertMention.resources','origin'])->asArray()->all();

    return array('data' => $mentions);

  }

}
