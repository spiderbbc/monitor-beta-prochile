<?php
namespace app\helpers;

use yii;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use app\models\Mentions;
use app\models\UsersMentions;
use yii\httpclient\Client;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * MentionsHelper wrapper for table db function.
 *
 */
class MentionsHelper
{
    /**
     * [saveMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveMencions($where = [], $properties = []){
       
      


        $is_model = Mentions::find()->where($where)->one();
        // if there a record 
        if($is_model){
            $model = Mentions::find()->where($where)->one();
            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // if not there a record
        if(is_null($is_model)){
            $model = new  Mentions();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }
        
        // save or update
        $model->save();

        return $model;

    }

     /**
     * [saveMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveUserMencions($where = [], $properties = []){
        

        $is_model = UsersMentions::find()->where($where)->one();
        // if there a record 
        if($is_model){
            $model = UsersMentions::find()->where($where)->one();
            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // if not there a record
        if(is_null($is_model)){
            $model = new  UsersMentions();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // save or update
        $model->save();

        return $model;

    }
    /**
     * [getGeolocation get geolocation by ip]
     * @param  string  $ip       [description]
     * @return [type]             [description]
     */
    public static function getGeolocation($ip){

        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('GET')
            ->setUrl("http://ip-api.com/json/{$ip}")
            ->setData(['fields' => '114713'])
            ->send();
            
        if ($response->isOk && $response->data['status'] == 'success') {
            return  $response->data;
        }
        return null;

    }

    public static function setNumberCommentsSocialMedia($alertId,$resourceSocialIds = []){
        $alerMentionsIds = \app\helpers\AlertMentionsHelper::getAlertsMentionsIdsByAlertIdAndResourcesIds($alertId,$resourceSocialIds);
        $total = 0;
        if(!empty($alerMentionsIds)){
            $db = \Yii::$app->db;
            $total = $db->cache(function ($db) use($alerMentionsIds){
                return \app\models\Mentions::find()->where(['alert_mentionId' => $alerMentionsIds])->count();
            },60);
        }

        return $total;    
    }

    public static function getDataMentionData($alertId,$resourceId,$targets){
        $alerMentionsIds = \app\helpers\AlertMentionsHelper::getAlertsMentionsIdsByAlertIdAndResourcesIds($alertId,$resourceId);
        // set targets
        $data = [];
        foreach ($targets as $target) {
            $data[$target] = 0;
        }
        
        if(!empty($alerMentionsIds)){
            $expression = '';
            for ($t=0; $t < sizeOf($targets) ; $t++) { 
                $expression .= "`mention_data`->'$.{$targets[$t]}' AS $targets[$t]";
                if(isset($targets[$t + 1])){
                    $expression.= ",";
                }
            }
            $expression = new \yii\db\Expression($expression);
            $db = \Yii::$app->db;
            $result = $db->cache(function ($db) use($alerMentionsIds,$expression){
                return (new \yii\db\Query)
                ->select($expression)
                ->from('mentions')
                ->where(['mentions.alert_mentionId' => $alerMentionsIds])->all();
            },60);
            
            if(!empty($result)){
                for ($r=0; $r < sizeof($result) ; $r++) { 
                    foreach ($result[$r] as $target => $value) {
                        if(!is_null($value)){
                            $data[$target] += $value;
                        }
                    }
                }
            } 
            
        }
        return $data;
    }

    public static function getColumnMentionGridView(){
        
        // if($grid){
        //     $gridColumns[] = ['class' => 'yii\grid\SerialColumn'];
        // }
        
        $gridColumns = [
            [
                'label' => Yii::t('app','Recurso Social'),
                'attribute' => 'resourceName',
                'format' => 'raw',
                'value' => function($model){
                    return $model['recurso'];
                }
            ],
            [
                'label' => Yii::t('app','term searched'),
                'attribute' => 'termSearch',
                'format' => 'raw',
                'value' => function($model){
                    return $model['term_searched'];
                }
            ],
            [
                'label' => Yii::t('app','fecha'),
                //'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return \Yii::$app->formatter->asDate($model['created_time'], 'yyyy-MM-dd');
                }
            ],
            [
                'label' => Yii::t('app','name'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model){
                    return $model['name'];
                }
            ],
            [
                'label' => Yii::t('app','screen_name'),
                'attribute' => 'screen_name',
                'format' => 'raw',
                'value' => function($model){
                    return $model['screen_name'];
                }
            ],
            [
                'label' => Yii::t('app','subject'),
                'attribute' => 'subject',
                'format' => 'raw',
                'value' => function($model){
                    return $model['subject'];
                }
            ],
            [
                'label' => Yii::t('app','message_markup'),
                'attribute' => 'message_markup',
                'format' => 'raw',
                'value' => function($model){
                    return $model['message_markup'];
                }
            ],
            [
                'label' => Yii::t('app','url'),
                //'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return \yii\helpers\Html::a('link',$model['url'],['target'=>'_blank', 'data-pjax'=>"0"]);
                }
            ],
        ]; 
        
        
        return $gridColumns;
    }

    public static function getColorResourceByName($resourceName)
    {
        $colors = [
            'Twitter' => '#3245ed',
            'Facebook Comments' => '#218bed',
            'Facebook Messages' => '#9ba2e0',
            'Instagram Comments' => '#e01f56',
            'Live Chat' => '#eb34e8',
            'Live Chat Conversations' => '#F18F11',
            'Paginas Webs' => '#bbc71c',
            'Noticias Webs' => '#1ee321',
            'Excel Document' => '#1ee321'
            
        ];
        return $colors[$resourceName];
    }

    public static function getPropertiesSourceBox($alertId){
        
        $model = \app\models\Alerts::findOne($alertId);
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



    public static function getCountSourcesMentions($alertId){
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
        
        //var_dump($data);
        if(is_null($data[0])){
        $data[0] = ['not found',0,0,0];
        }

        $colors = ['#3CAAED','#EC1F2E','#3A05BD'];
        
        return array('status'=>true,'data'=>$data,'colors' => $colors);
    }


    public static function getProductInteration($alertId,$resourceId = null){
        $model = \app\models\Alerts::findOne($alertId);
        $where['alertId'] = $model->id;
        if(!is_null($resourceId)){
            $where['resourcesId'] = $resourceId;
        }
        $alerts_mentions = \app\models\AlertsMencions::find()->where($where)->all();
        // get products
        $products = [];
        foreach ($alerts_mentions as $alerts_mention) {
        $products[$alerts_mention->term_searched][$alerts_mention->resources->name][] = $alerts_mention->id;
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
            $dataCount[] = array($product,$shares,$likes,$total);
        }
       
        if(!count($dataCount)){
            $dataCount[] = array('Not Found',0,0,0);
        }else{
            // get top terms more total value
            usort($dataCount, function($a, $b) {
                return end($b) - end($a);
            });
            $dataCount = array_slice($dataCount, 0, 10);
        }
        $colors = ['#3CAAED','#EC1F2E','#3A05BD'];
        return array('status'=>true,'data' => $dataCount,'colors' => $colors);
    }

    public static function getDataMentions($alertId){

        $db = \Yii::$app->db;
        $duration = 60;
        
        $where['alertId'] = $alertId;
        if(isset($params['resourceId'])){
            $where['resourcesId'] = $params['resourceId'];
        }
        // if resourceId if not firts level on params
        if(isset($params['MentionSearch']['resourceId'])){
            $where['resourcesId'] = $params['MentionSearch']['resourceId'];
        }
       
        $alertMentions = $db->cache(function ($db) use ($where) {
          return (new \yii\db\Query())
            ->select('id')
            ->from('alerts_mencions')
            ->where($where)
            ->orderBy(['resourcesId' => 'ASC'])
            ->all();
        },$duration); 
        
        $alertsId = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');  
        
        $rows = (new \yii\db\Query())
        ->cache($duration)
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
        ->orderBy(['m.created_time' => 'ASC']);
        //->all();
        $data = [];    
        if($rows){
            foreach($rows->batch() as $mentions){
                for ($r=0; $r < sizeOf($mentions) ; $r++) { 
                    if(isset($mentions[$r]['created_time'])){
                        $mentions[$r]['created_time'] =  \Yii::$app->formatter->asDate($mentions[$r]['created_time'], 'yyyy-MM-dd');
                    }
                    $data[] = $mentions[$r];
                }
            }
        }

        return $data;
    }
    
    
    public static function getMentionOnDate($alertId,$js = true){
        // get models
        $model = \app\models\Alerts::findOne($alertId);
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
                $date = ($js) ? (int) strtotime($data[$d]['date_created']) * 1000 : (int) strtotime($data[$d]['date_created']);
                $model[$index]['data'][] = array((int)$date,(int)$data[$d]['total']);
            }
            $model[$index]['color'] = \app\helpers\MentionsHelper::getColorResourceByName($resourceName);
            $index++;
        }
        }
        return array('status'=>true,'model' => $model);  
    }


    public static function getDomainsFromMentionsOnUrls($alertId,$resourceId = null,$term = null,$socialId = null){

        $properties['alertId'] = $alertId;
        if(!is_null($resourceId)){
            $properties['resourcesId'] = $resourceId;
        }

        if($term != ''){
            $properties['term_searched'] = $term;
        }
       
        $alertMentions = \app\helpers\AlertMentionsHelper::getAlersMentions($properties);
        $totalDomains = [];
        
        if(!is_null($alertMentions)){
            
            $alertMentionsIds = \yii\helpers\ArrayHelper::getColumn($alertMentions,'id');
            $where['alert_mentionId'] = $alertMentionsIds;
            if($socialId != ''){
                $where['social_id'] = $socialId;
            }

            $urls = \app\models\Mentions::find()->select(['domain_url'])->where($where)
            //->cache(20)
            ->Andwhere(['IS NOT', 'domain_url', null])
            ->asArray()
            ->all();
           
            foreach($urls as $index => $values){
                $domain = $values['domain_url'];
                if(!in_array($domain,array_keys($totalDomains))){
                    $totalDomains[$domain] = 1;
                }else{
                    $count = $totalDomains[$domain];
                    $totalDomains[$domain] = $count + 1;
                }
            }
            arsort($totalDomains);

        }
        return $totalDomains;
    }

    public static function getCountMentions($model){
        $data = [];
        if($model){
            $count = (new \yii\db\Query())
            ->cache(10)
            ->from('alerts_mencions')
            ->join('JOIN', 'mentions', 'mentions.alert_mentionId = alerts_mencions.id')
            ->where(['alertId' => $model->id])
            ->count();
            // total register
            $data['count'] = (int)$count;
        }
        
        return [
            'data' => $data,
        ];
    }

    
}