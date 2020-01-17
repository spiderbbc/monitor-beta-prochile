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
    // chage values to int
    for($d = 0; $d < sizeof($data); $d++){
      if(!is_null($data[$d])){
        for ($r=0; $r <sizeof($data[$d]) ; $r++) { 
          if(is_numeric($data[$d][$r])){
            $data[$d][$r] = intval($data[$d][$r]);
          }
        }
      }
    }

    if(is_null($data[0])){
      $data[0] = ['not found',0,0,0,0];
    }

    
    return array('status'=>true,'data'=>$data);

  }
  /**
   * [actionTopPostInteration top post face or instagram with more interation]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionTopPostInteration($alertId)
  {
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    $model = \app\models\Alerts::findOne($alertId);
    $data = [];
    foreach ($model->config->sources as $sources){
      $data[] = \app\helpers\AlertMentionsHelper::getPostInteractions($sources->name,$sources->id,$model->id);
    }

    // reorder array

    $model = [];
    for ($d=0; $d <sizeof($data) ; $d++) { 
      if(!is_null($data[$d])){
        for ($s=0; $s <sizeof($data[$d]) ; $s++) { 
          if(is_numeric($data[$d][$s])){
            $data[$d][$s] = intval($data[$d][$s]);
          }
          $model[] = $data[$d][$s];
        }
      }
    }

    if(empty($model)){
      $model[] = ['not found',0,0,0,0];
    }

    return array('status'=>true,'data'=>$model);

  }

  public function actionProductInteration($alertId)
  {
    \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
    $model = \app\models\Alerts::findOne($alertId);
    $alerts_mentions = \app\models\AlertsMencions::find()->where(['alertId' => $model->id])->all();

    // get products
    $products = [];
    foreach ($alerts_mentions as $alerts_mention) {
      if($alerts_mention->mentionsCount){
        $model =  \app\helpers\AlertMentionsHelper::getProductByTermSearch($alerts_mention->term_searched);
        if(!is_null($model)){
          $products[$model->name][$alerts_mention->resources->name][] = $alerts_mention->id;
        }//
      }// end if
    }// end foreach
    $data = [];
    foreach ($products as $product => $resourceNames) {
      # code...
      foreach ($resourceNames as $resourceName => $alerts_mention_ids) {
        # code ..
        $data[$product][] = \app\helpers\AlertMentionsHelper::getProductInterations($resourceName,$alerts_mention_ids,$alertId);
      }
    }

    //reorder data
    $properties = ['shares','likes','total','like_post','favorite_count','retweets','likes_twitter'];
    $dataCount = [];
    foreach ($data as $product => $values) {
        $total = 0;
        $shares = 0;
        $likes = 0;
        $like_post = 0;
        $favorite_count = 0;
        $retweets = 0;
        $likes_twitter = 0;
        foreach ($values as $value) {
          $shares += (isset($value['shares'])) ? $value['shares']: 0;
          $likes  += (isset($value['likes'])) ? $value['likes']: 0;
          $like_post  += (isset($value['like_post'])) ? $value['like_post']: 0;
          $favorite_count  += (isset($value['favorite_count'])) ? $value['favorite_count']: 0;
          $retweets  += (isset($value['retweets'])) ? $value['retweets']: 0;
          $likes_twitter  += (isset($value['likes_twitter'])) ? $value['likes_twitter']: 0;
          $total  += (isset($value['total'])) ? $value['total']: 0;
        }
        $dataCount[] = array($product,$shares,$like_post,$likes,$favorite_count,$retweets,$likes_twitter,$total);
    }





    return array('status'=>true,'resources'=> $data,'data' => $dataCount);
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
