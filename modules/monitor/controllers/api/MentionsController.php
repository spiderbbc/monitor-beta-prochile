<?php

namespace app\modules\monitor\controllers\api;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class MentionsController extends \yii\web\Controller
{

    public function actionIndex($alertId)
    {
       \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
       
		
       
       /*
		
       

       

       
       */

       

        

       	// menciones por producto por recurso y por fecha
       	

    }

  /**
   * [actionCountMentions return count the total mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountMentions($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    
    if($this->findModel($alertId)){
      // cuenta si la alerta tiene menciones
      $rows = (new \yii\db\Query())
      ->from('alerts_mencions')
      ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
      ->where(['alertId' => $alertId])
      ->count();
      return array('status'=>true,'count'=>$rows);

    }
  }

  public function actionCountSourcesMentions($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    // cuenta por menciones
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'DESC'])->all();
    $resourceCount = [];
    foreach ($alertMentions as $alertMention){
      $mentionCount = \app\models\Mentions::find()->where(['alert_mentionId' => $alertMention->id])->count(); 
      $mentionsModel = ['count' => $mentionCount,'resourcesId' => $alertMention->resources->id];
      $resourceCount[$alertMention->resources->name][] = $mentionCount;
      /*$resourceCount[$alertMention->resources->name][] = $mentionsModel;*/
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

  /**
   * [actionGetResourceId return id from resource]
   * @param  [type] $resourceName [description]
   * @return [type]               [description]
   */
  public function actionGetResourceId($resourceName){
    
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
     $model = \app\models\Resources::find()->where(['name' => $resourceName])->one(); 

    return array('status'=>true,'resourceId'=>$model->id);

  }


  public function actionListWords($alertId){
   \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

    $keywords = \app\models\Keywords::find()->where(['alertId' => $alertId])->all();

    $wordsModel = [];
    $index = 0;
    foreach ($keywords as $keyword){
      if($keyword->keywordsMentions){
        $wordsModel[$index]['text']      = $keyword->name;
        $wordsModel[$index]['weight']    = $keyword->getKeywordsMentions()->count();
        //$wordsModel[$index]['link']      = \yii\helpers\Url::to(['alert/view-word','keywordId' => $keyword->id,'alertId'=> $alertId],true);
        //$wordsModel[$index]['handlers']      = ['click: function() {$("#list-mentions").DataTable().search('.$keyword->name.').draw()}'];
        $index++; 
      }
    }

    return array('status'=>true,'wordsModel' => $wordsModel);
  }


  public function actionMentionOnDate($alertId){
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $alertsMencionsId = [];
    foreach ($alertMentions as $alertMention){
      if($alertMention->mentionsCount){
        $alertsMencionsId[] = $alertMention->id;
      }
    }
    // menciones por fecha
    $expression = new Expression("DATE(FROM_UNIXTIME(created_time)) AS date,COUNT(*) AS total");
    $expressionGroup = new Expression(" DATE(FROM_UNIXTIME(created_time))");



    $rows = (new \yii\db\Query())
      ->select($expression)
      ->from('mentions')
      ->where(['alert_mentionId' => $alertsMencionsId])
      ->groupBy($expressionGroup)
      ->all();

    return array('status'=>true,'rows' => $rows);  
  }



  public function actionResourceOnDate($alertId){
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    //menciones por recurso y fecha
    $expression = new Expression("DATE(FROM_UNIXTIME(created_time)) AS date,COUNT(*) AS total");
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
          ->orderBy('total DESC')
          ->groupBy($expressionGroup)
          ->all();

          foreach ($rows as $row){
            $row['product_searched'] = $alertMention->term_searched;
            $resourceDateCount[$alertMention->resources->name][] = $row;  
          }

          
        } // end if in_array

      }// is not null 
      
    }// end foreach
    return array('status'=>true,'resourceDateCount' => $resourceDateCount);  
  }

  /**
   * Finds the Alerts model based on its primary key value.
   * If the model is not found, a 404 HTTP exception will be thrown.
   * @param integer $id
   * @return Alerts the loaded model
   * @throws NotFoundHttpException if the model cannot be found
   */
  protected function findModel($id)
  {
      if (($model = \app\models\Alerts::findOne($id)) !== null) {
          return $model;
      }

      throw new NotFoundHttpException('The requested page does not exist.');
  }

}
