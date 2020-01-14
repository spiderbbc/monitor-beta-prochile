<?php

namespace app\modules\monitor\controllers\api;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class MentionsController extends \yii\web\Controller
{
  /**
   * [actionIndex action to the index view]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionIndex(){
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    $basePath = \yii::$app->basePath;
    shell_exec("php {$basePath}/yii daemon/alerts-run 2>&1");
    shell_exec("php {$basePath}/yii daemon/data-search 2>&1");
    return array('status'=>true);

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
  /**
   * [actionCountSourcesMentions count by sources]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountSourcesMentions($alertId){

    
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    // cuenta por menciones
    $model = \app\models\Alerts::findOne($alertId);
    $data = [];

    foreach ($model->config->sources as $sources){

      if(!\app\helpers\StringHelper::in_array_r($sources->name,$data)){
          $data[] = \app\helpers\AlertMentionsHelper::getSocialNetworkInteractions($sources->name,$sources->id,$model->id);

      }
      
    }

    var_dump($data);
    
  //return array('status'=>true,'data'=>$data);

  }
  /**
   * [actionCountByProducts count mentions by products]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
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
  /**
   * [actionListMentions list all mention by id]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
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

  /**
   * [actionListWords list words found it]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListWords($alertId){
   \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

    $keywords = \app\models\Keywords::find()->where(['alertId' => $alertId])->all();

    $wordsModel = [];
    $index = 0;
    foreach ($keywords as $keyword){
      if($keyword->keywordsMentions){
        $wordsModel[$index]['text']      = $keyword->name;
        $wordsModel[$index]['weight']    = $keyword->getKeywordsMentions()->count();
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
   * [actionListEmojis list emojis count in mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListEmojis($alertId){

    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;

    // list mentions: mentions
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $alertsId = [];
    foreach ($alertMentions as $alertMention){
      if($alertMention->mentionsCount){
        $alertsId[] = $alertMention->id;
      }
    }

    $mentions = \app\models\Mentions::find()->select(['id','message'])->where(['alert_mentionId' => $alertsId])->asArray()->all();
    $model = [];
    foreach ($mentions as $mention){
      $emojis = \Emoji\detect_emoji($mention['message']);
      if(!empty($emojis)){
          foreach($emojis as $emoji){
            $name = $emoji['short_name'];
            if(isset($model[$name])){
              $model[$name]['count'] += 1;
              
            }else{
              $emoji = $emoji['emoji'];
              $model[$name] = ['count' => 1,'emoji' => $emoji ];
            }
          }
      }
    }

    return array('data' => $model);    

  }
  /**
   * [actionStatusAlert return a list wiht social media and his status]
   * @param  [int] $alertId [id of the alert]
   * @return [json]          [list social media and his status]
   */
  public function actionStatusAlert($alertId)
  {
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    $model =  \app\models\HistorySearch::findOne(['alertId' => $alertId]);

    return array('data' => $model);  
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
