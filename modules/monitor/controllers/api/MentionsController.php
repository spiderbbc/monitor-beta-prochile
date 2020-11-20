<?php

namespace app\modules\monitor\controllers\api;

use yii\rest\Controller;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\Response;
use yii\web\NotFoundHttpException;

class MentionsController extends Controller
{

  public function behaviors(){
    return [
        [
            'class' => 'yii\filters\ContentNegotiator',
            'only' => [
              'status-alert',
              'count-mentions',
              'properties-source-box',
              
              'box-sources-count',
              'count-sources-mentions',
              'top-post-interation',
              'product-interation',
              'mention-on-date',
              'list-mentions',
              'list-words',
              'list-emojis'
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
   * [actionIndex action to the index view]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionIndex(){
   
    $basePath = \yii::$app->basePath;
    // shell_exec("php {$basePath}/yii daemon/alerts-run 2>&1");
    // shell_exec("php {$basePath}/yii daemon/data-search 2>&1");
    return array('status'=>true);

  }

  /**
   * [actionCountMentions return count the total mentions / call component vue: total-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountMentions($alertId){
   
    $model = $this->findModel($alertId);
    $data = [];

    if($model){
      $count = (new \yii\db\Query())
      ->cache(10)
      ->from('alerts_mencions')
      ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
      ->where(['alertId' => $alertId])
      ->count();
      
      // total register
      $data['count'] = (int)$count;
    }
    
    return [
      'data' => $data,
    ];
  }

  public function actionPropertiesSourceBox($alertId){

    $model = $this->findModel($alertId);
    $alertResources = \yii\helpers\ArrayHelper::map($model->config->sources,'id','name');

    $data = [];
    // send query
    $data_search = [];
    $query = (new \yii\db\Query())
     ->select(['mention_data'])
     ->from('alerts_mencions')
     ->where(['alertId' => $alertId])
     ->andWhere(['not', ['mention_data' => null]]);

    foreach($query->batch() as $alertMention){
      $data_search[]= $alertMention;
    }
     
    $data = [];
    //$data = \app\helpers\AlertMentionsHelper::setMentionData($data_search);
     
    if(in_array('Facebook Comments',array_values($alertResources))){
      $data['total_comments_facebook_comments'] = (int) \app\helpers\MentionsHelper::setNumberCommentsSocialMedia($model->id,array_search('Facebook Comments',$alertResources));
      
    }
     
    if(in_array('Facebook Messages',array_values($alertResources))){
      $data['total_inbox_facebook'] = (int) \app\helpers\AlertMentionsHelper::getCountAlertMentionsByResourceId($model->id,array_search('Facebook Messages',$alertResources));
    }

    if(in_array('Instagram Comments',array_values($alertResources))){
      $instagramId = array_search('Instagram Comments',$alertResources);
      $data['total_comments_instagram'] =  (int)\app\helpers\MentionsHelper::setNumberCommentsSocialMedia($model->id,$instagramId);
    }

    if(in_array('Twitter',array_values($alertResources))){
      
      $twitterId = array_search('Twitter',$alertResources);
      $db = \Yii::$app->db;
      $duration = 15; 
      $where = ['alertId' => $model->id,'resourcesId' => $twitterId];

      $alertMentions = $db->cache(function ($db) use ($where) {
        return (new \yii\db\Query())
          ->select('id')
          ->from('alerts_mencions')
          ->where($where)
          ->orderBy(['resourcesId' => 'ASC'])
          ->all();
      },$duration); 
      
      $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id'); 

      $totalCount = (new \yii\db\Query())
          ->from('mentions m')
          ->where(['alert_mentionId' => $alertsId])
          ->join('JOIN','alerts_mencions a', 'm.alert_mentionId = a.id')
          ->count();
      
      $data['total_tweets'] = (int)$totalCount;
      
    }

    if(in_array('Live Chat',array_values($alertResources))){
      $livechatTicketId = array_search('Live Chat',$alertResources);
      $db = \Yii::$app->db;
      $duration = 15; 
      $where = ['alertId' => $model->id,'resourcesId' => $livechatTicketId];

      $alertMentionsIds = $db->cache(function ($db) use ($where) {
          $ids =\app\models\AlertsMencions::find()->select(['id','alertId'])->where($where)->asArray()->all();
          return array_keys(\yii\helpers\ArrayHelper::map($ids,'id','alertId'));
      },$duration); 

      $mentionWhere = ['alert_mentionId' => $alertMentionsIds];

      $expression = new Expression("`mention_data`->'$.id' AS ticketId");
      // count number tickets
      // SELECT `mention_data`->'$.id' AS ticketId FROM `mentions` where alert_mentionId = 9 GROUP BY `ticketId` DESC
      $ticketCount = (new \yii\db\Query())
          ->cache($duration)
          ->select($expression)
          ->from('mentions')
          ->where($mentionWhere)
          ->groupBy(['ticketId'])
          ->count();

      $data['total_tickets'] = (int)$ticketCount;    
    }

    if(in_array('Live Chat Conversations',array_values($alertResources))){
      $livechatId = array_search('Live Chat Conversations',$alertResources);
      $db = \Yii::$app->db;
      $duration = 15; 
      $where = ['alertId' => $model->id,'resourcesId' => $livechatId];

      $alertMentionsIds = $db->cache(function ($db) use ($where) {
          $ids =\app\models\AlertsMencions::find()->select(['id','alertId'])->where($where)->asArray()->all();
          return array_keys(\yii\helpers\ArrayHelper::map($ids,'id','alertId'));
      },$duration); 

      $expression = new Expression("`mention_data`->'$.event_id' AS eventId");
  
      $mentionWhere = ['alert_mentionId' => $alertMentionsIds];
      // count number tickets
      // SELECT `mention_data`->'$.event_id' AS eventId FROM `mentions` where alert_mentionId = 9 GROUP BY `eventId` DESC
      $chatsCount = (new \yii\db\Query())
          ->cache($duration)
          ->select($expression)
          ->from('mentions')
          ->where($mentionWhere)
          ->groupBy(['eventId'])
          ->count();
  

      $data['total_chats'] = (int)$chatsCount;
    }

    if(in_array('Paginas Webs',array_values($alertResources))){
      $webPageId = array_search('Paginas Webs',$alertResources);
      $db = \Yii::$app->db;
      $duration = 15; 
      $where = ['alertId' => $model->id,'resourcesId' => $webPageId];

      $alertMentions = $db->cache(function ($db) use ($where) {
          return \app\models\AlertsMencions::find()->where($where)->all();
      },$duration); 
      
      $data['total_web_records_found'] = 0;

      foreach ($alertMentions as $alertMention) {
          if($alertMention->mentions){
            $data['total_web_records_found'] += $alertMention->mentionsCount;
          }
      }
    }

    if(in_array('Noticias Webs',array_values($alertResources))){
      $newId = array_search('Noticias Webs',$alertResources);
      $db = \Yii::$app->db;
      $duration = 15; 
      $where = ['alertId' => $model->id,'resourcesId' => $newId];

      $alertMentions = $db->cache(function ($db) use ($where) {
          return \app\models\AlertsMencions::find()->where($where)->all();
      },$duration); 
      
      $data['total_web_news_found'] = 0;

      foreach ($alertMentions as $alertMention) {
          if($alertMention->mentions){
            $data['total_web_news_found'] += $alertMention->mentionsCount;
          }
      }
    }
    
    return [
      'data' => $data
    ];
     
  }
  /**
   * [actionBoxSourcesCount description]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionBoxSourcesCount($alertId)
  {
   
    $model = $this->findModel($alertId);
    $modelDataCount = [];
    
    foreach ($model->config->sources as $sources){
      if(!\app\helpers\StringHelper::in_array_r($sources->name,$modelDataCount)){
          $modelDataCount[] = \app\helpers\AlertMentionsHelper::getSocialNetworkInteractions($sources->name,$sources->id,$model->id);
      }
    }
    $data = [];

    for($d = 0; $d < sizeof($modelDataCount); $d++){
      if(!is_null($modelDataCount[$d])){
        $name = $modelDataCount[$d][0];
        $total = $modelDataCount[$d][4];
        
        $data[] = array($name,$total);
      }
    }



    return array('status' => true,'data' => $data,'modelDataCount' => $modelDataCount);
  }

  /**
   * [actionCountSourcesMentions count by sources / call component vue: total-resources-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountSourcesMentions($alertId){
    // cuenta por menciones
    $model = $this->findModel($alertId);
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
    
    //var_dump($data);
    if(is_null($data[0])){
      $data[0] = ['not found',0,0,0];
    }

    $colors = ['#3CAAED','#EC1F2E','#3A05BD'];
    
    return array('status'=>true,'data'=>$data,'colors' => $colors);

  }
  /**
   * [actionTopPostInteration top post face or instagram with more interation / call component vue: post-interation-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionTopPostInteration($alertId)
  {
   
    $status = true;
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
      $model[] = ['not found',0,0,0,0,0];
      $status = false;
    }

    return array('status'=>$status,'data'=>$model);

  }

  /**
   * [actionProductInteration interations by products / call component vue: products-interations-chart]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionProductInteration($alertId)
  {
   
    $model = \app\models\Alerts::findOne($alertId);
    $alerts_mentions = \app\models\AlertsMencions::find()->where(['alertId' => $model->id])->all();
    // get products
    $products = [];
    foreach ($alerts_mentions as $alerts_mention) {
      if($alerts_mention->mentionsCount){
        $products[$alerts_mention->term_searched][$alerts_mention->resources->name][] = $alerts_mention->id;
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
    $dataCount = [];
    foreach ($data as $product => $values) {
        $total = 0;
        $shares = null;
        $likes = 0;
        foreach ($values as $value) {
          // add shares and retweets
          $shares += (isset($value['shares'])) ? $value['shares']: 0;
          $shares  += (isset($value['retweets'])) ? $value['retweets']: 0;
          // add likes post and favorites
          $likes  += (isset($value['like_post'])) ? $value['like_post']: 0;
          $likes  += (isset($value['likes_twitter'])) ? $value['likes_twitter']: 0;
          // get total
          $total  += (isset($value['total'])) ? $value['total']: 0;
        }
        if($total >= 2){
          $dataCount[] = array($product,$shares,$likes,$total);
        }
    }
    
    if(!count($dataCount)){
      $dataCount[] = array('Not Found',0,0,0);
    }
    $colors = ['#3CAAED','#EC1F2E','#3A05BD'];
    return array('status'=>true,'data' => $dataCount,'colors' => $colors);
  }


  public function actionResourceOnDateChart($alertId)
  {
   
    $model = \app\models\Alerts::findOne($alertId);
    $alerts_mentions = \app\models\AlertsMencions::find()->where(['alertId' => $model->id])->all();

    // get resources with mentions
    $resources = [];
    foreach ($alerts_mentions as $alerts_mention) {
      if($alerts_mention->mentionsCount){
        $resources[] = $alerts_mention->resources->name;
      }// end if
    }// end foreach

    // get data by date for each resources
    $data = [];

    return array('status'=>true,'resources'=> $resources,'data' => $data);
  }


  /**
   * [actionListMentions list all mention by id / call component vue: list-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListMentions($alertId){
    // list mentions: resource - products - author - mentions
    $alertMentions = \app\models\AlertsMencions::find()->where(['alertId' => $alertId])->orderBy(['resourcesId' => 'ASC'])->all();
    $alertsId = [];
    foreach ($alertMentions as $alertMention){
      if($alertMention->mentionsCount){
        $alertsId[] = $alertMention->id;
      }
    }

    $db = \Yii::$app->db;
    $duration = 60;
    $rows = $db->cache(function ($db) use ($alertsId) {
        return (new \yii\db\Query())
                ->select([
                  'recurso' => 'r.name',
                  'term_searched' => 'a.term_searched',
                  'created_time' => 'm.created_time',
                  'name' => 'u.name',
                  'screen_name' => 'u.screen_name',
                  'subject' => 'm.subject',
                  'message_markup' => 'm.message_markup',
                  'url' => 'm.url',
                ])
                ->from('mentions m')
                ->where(['alert_mentionId' => $alertsId])
                ->join('JOIN','alerts_mencions a', 'm.alert_mentionId = a.id')
                ->join('JOIN','resources r', 'r.id = a.resourcesId')
                ->join('JOIN','users_mentions u', 'u.id = m.origin_id')
                ->all();

    },$duration);

    return array('data' => $rows);

  }

  /**
   * [actionListWords list words found it / call component vue: cloud-words]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListWords($alertId){
  

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

  /**
   * [actionResourceOnDate description / call component vue: resource-date-mentions]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionResourceOnDate($alertId){
   
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
   * [actionListEmojis list emojis count in mentions / call component vue: list-emojis]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionListEmojis($alertId){

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
            $name = (string)$emoji['short_name'];
            if(isset($model[$name])){
              $model[$name]['count'] += 1;
              
            }else{
              $emoji = strval($emoji['emoji']);
              $model[$name] = ['count' => 1,'emoji' => $emoji ];
            }
          }
      }
    }
    uasort($model,function($a,$b)
    {
      return ($a["count"] < $b["count"]) ? 1 : -1;
    });
    
    return array('data' =>array_values($model));    

  }

   /**
   * [actionStatusAlert return a list wiht social media and his status / call component vue: status-alert and modal-alert]
   * @param  [int] $alertId [id of the alert]
   * @return [json]          [list social media and his status]
   */
  public function actionStatusAlert($alertId)
  {
    $model = \app\models\HistorySearch::getDb()->cache(function ($db) use ($alertId) {
      return \app\models\HistorySearch::find()->select('search_data')->where(['alertId' => $alertId])->one();
    });
    return array('data' => $model);  
  }

 

  /**
   * [actionCountByProducts count mentions by products]
   * @param  [type] $alertId [description]
   * @return [type]          [description]
   */
  public function actionCountByProducts($alertId){

   
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
   * [actionGetResourceId return id from resource]
   * @param  [type] $resourceName [description]
   * @return [type]               [description]
   */
  public function actionGetResourceId($resourceName){
    
   
     $model = \app\models\Resources::find()->where(['name' => $resourceName])->one(); 

    return array('status'=>true,'resourceId'=>$model->id);

  }



  /**
   * [actionMentionOnDate return array of date to graph]
   * @param  [int] $id            [id of alert]
   * @return [array \ Exception]  [array of date or exception if alert id not exists]
   */
  public function actionMentionOnDate($alertId){
    // get models
    $model = $this->findModel($alertId);
    // get resources
    $alertResources = \yii\helpers\ArrayHelper::map($model->config->sources,'id','name');
    //menciones por recurso y fecha
    $expression = new Expression("r.name,DATE(FROM_UNIXTIME(created_time)) AS date_created,COUNT(*) AS total");
    // menciones por recurso y fecha para los chats
    $expressionChats = new Expression("r.name,DATE(FROM_UNIXTIME(created_time)) AS date_created,COUNT( DISTINCT social_id) AS total");
    
    // query by target resourceName chats
    $target = ['Facebook Messages','Live Chat Conversations','Live Chat'];
    $chatsIds = [];
    $commentsIds = [];
    foreach($alertResources as $id => $resourceName){
      if(in_array($resourceName,$target)){
        $chatsIds[] = $id;
      }else{
        $commentsIds[] = $id;
      }
    }
    
    $rowsChats = [];
    if(count($chatsIds)){
      // get alertMentions and ids
      $alertMentions = \app\models\AlertsMencions::find()->select('id')->where(['alertId' => $alertId,'resourcesId' => $chatsIds])->orderBy(['resourcesId' => 'ASC'])->asArray()->all();
      $alertMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertMentions, 'id');
      $rowsChats = (new \yii\db\Query())
        ->select($expressionChats)
        ->from('mentions')
        ->where(['alert_mentionId' => $alertMentionsIds])
        ->join('JOIN','alerts_mencions a', 'alert_mentionId = a.id')
        ->join('JOIN','resources r', 'r.id = a.resourcesId')
        ->orderBy('date_created ASC')
        ->groupBy(['date_created','r.name'])
        ->all(); 
    }
    $rowsComments = [];
    if(count($commentsIds)){
      // get alertMentions and ids
      $alertMentions = \app\models\AlertsMencions::find()->select('id')->where(['alertId' => $alertId,'resourcesId' => $commentsIds])->orderBy(['resourcesId' => 'ASC'])->asArray()->all();
      $alertMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertMentions, 'id');
      $rowsComments = (new \yii\db\Query())
        ->select($expression)
        ->from('mentions')
        ->where(['alert_mentionId' => $alertMentionsIds])
        ->join('JOIN','alerts_mencions a', 'alert_mentionId = a.id')
        ->join('JOIN','resources r', 'r.id = a.resourcesId')
        ->orderBy('date_created ASC')
        ->groupBy(['date_created','r.name'])
        ->all();

        
    }
    // merge both arrays
    $rows = ArrayHelper::merge($rowsChats, $rowsComments);
   
    $result = ArrayHelper::index($rows, null, 'name');
    // compose array to higchart  
    $model = array();
    $index = 0; 
    foreach ($result as $resourceName => $data){
      if(count($data)){
        $model[$index]['name'] = $resourceName;
        for($d = 0; $d < sizeOf($data); $d++){
          $date = (int) strtotime($data[$d]['date_created']) * 1000;
          $model[$index]['data'][] = array((int)$date,(int)$data[$d]['total']);
        }
        $model[$index]['color'] = \app\helpers\MentionsHelper::getColorResourceByName($resourceName);
        $index++;
      }
    }
    return array('status'=>true,'model' => $model);  
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
