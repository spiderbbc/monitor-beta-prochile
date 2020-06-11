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
	
}