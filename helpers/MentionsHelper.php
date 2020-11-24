<?php
namespace app\helpers;

use yii;
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
        
        return [
        'data' => $data
        ];
    }

    public static function getCountSourcesMentions($alertId){
       
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
    
    public static function getProductInteration($alertId){
        
        $model = \app\models\Alerts::findOne($alertId);
        $alerts_mentions = \app\models\AlertsMencions::find()->where(['alertId' => $model->id])->all();

        // get products
        $products = [];
        foreach ($alerts_mentions as $alerts_mention) {
        if($alerts_mention->mentionsCount){
            /*$product_model =  \app\helpers\AlertMentionsHelper::getProductByTermSearch($alerts_mention->term_searched);
            if(!is_null($product_model)){
            $products[$product_model->name][$alerts_mention->resources->name][] = $alerts_mention->id;
            }//*/
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
            $like_post = 0;
            $retweets = 0;
            $likes_twitter = 0;
            foreach ($values as $value) {
            $shares += (isset($value['shares'])) ? $value['shares']: 0;
            /*if(isset($value['shares'])){
                if(intval($value['shares'])){
                $shares += $value['shares'];
                }
            }*/
            $likes  += (isset($value['likes'])) ? $value['likes']: 0;
            $like_post  += (isset($value['like_post'])) ? $value['like_post']: 0;
            $retweets  += (isset($value['retweets'])) ? $value['retweets']: 0;
            $likes_twitter  += (isset($value['likes_twitter'])) ? $value['likes_twitter']: 0;
            $total  += (isset($value['total'])) ? $value['total']: 0;
            }
            $dataCount[] = array($product,$shares,$like_post,$likes,$retweets,$likes_twitter,$total);
        }

        if(!count($dataCount)){
        $dataCount[] = array('Not Found',0,0,0,0,0,0);
        }



        return array('status'=>true,'resources'=> $data,'data' => $dataCount);
    }
}