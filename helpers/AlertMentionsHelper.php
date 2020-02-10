<?php
namespace app\helpers;

use yii;
use yii\db\Expression;

/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

/**
 * AlertMentionsHelper wrapper for table db function.
 *
 */
class AlertMentionsHelper
{
    /**
     * [saveAlertsMencions save in alerts_mencions model]
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    public static function saveAlertsMencions($where = [], $properties = []){

        $is_model = \app\models\AlertsMencions::find()->where($where)->one();
        // if there a record 
        if($is_model){
            $model = \app\models\AlertsMencions::find()->where($where)->one();
            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }

        // if not there a record
        if(is_null($is_model)){
            $model = new  \app\models\AlertsMencions();

            foreach($where as $property => $value){
                $model->$property = $value;
            }

            foreach($properties as $property => $value){
                $model->$property = $value;
            }
        }
        return ($model->save()) ? $model : false;

    }
    /**
     * [getAlersMentions get the alerts previus mentions call]
     * @return [obj / null] [the objects db query]
     */
    public static function getAlersMentions($properties = []){
        $alertsMencions = \app\models\AlertsMencions::find()->where($properties)->asArray()->all();

        return (!empty($alertsMencions)) ? $alertsMencions : null;
    }

    /**
     * [isAlertsMencionsExists if a mention alert exits]
     * @param  [type]  $publication_id [description]
     * @return boolean                 [description]
     */
    public static function isAlertsMencionsExists($publication_id){
        if(\app\models\AlertsMencions::find()->where( [ 'publication_id' => $publication_id] )->exists()){
            return true;
        }
        return false;
    }


    /**
     * [isAlertsMencionsExists if a mention alert exits by property]
     * @param  [type]  $publication_id [description]
     * @return boolean                 [description]
     */
    public static function isAlertsMencionsExistsByProperties($where){
        if(\app\models\AlertsMencions::find()->where($where)->exists()){
            return true;
        }
        return false;
    }


    /**
     * [getSocialNetworkInteractions return array of social with interation]
     * @param  [type] $resource_name [description]
     * @param  [type] $resource_id   [description]
     * @param  [type] $alertId       [description]
     * @return [type]                [description]
     */
    public static function getSocialNetworkInteractions($resource_name,$resource_id,$alertId)
    {

        switch ($resource_name) {
            
            case 'Facebook Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return array($resource_name,$model->shareFaceBookPost,'0',$model->likesFacebookComments,$model->total);
                break;

            case 'Facebook Messages':
                $count = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'resourcesId' => $resource_id])->count();
                /*$model->alertId = $alertId;
                $model->resourcesId = $resource_id;*/
                
                return array($resource_name,'0','0','0',$count);
                break;    

            case 'Instagram Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return array($resource_name,'0',$model->likesInstagramPost,$model->likesFacebookComments,$model->total);
                break;
            case 'Twitter':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;

                return array($resource_name,$model->twitterRetweets,'0',$model->twitterLikes,$model->twitterTotal);
            
                break;
            case 'Live Chat':
                $models = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'resourcesId' => $resource_id])->all();
                $expression = new Expression("`mention_data`->'$.id' AS ticketId");
                $total = 0;

                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('ticketId')
                      ->count();
                    $total += intval($rows);  
                }


                return array($resource_name,'0','0','0',$total);

                break;

            case 'Live Chat Conversations':
                $models = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'resourcesId' => $resource_id])->all();
                $total = 0;
                foreach ($models as $model) {
                    $total += $model['mention_data']['count'];
                }

                return array($resource_name,'0','0','0',$total);

                break;  
            case 'Excel Document':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;

                return array($resource_name,'0','0','0',$model->twitterTotal);
                break;                  

            
            default:
                # code...
                return  null;
                break;
        }
    }
	/**
     * [getPostInteractions return post interations by social]
     * @param  [type] $resource_name [description]
     * @param  [type] $resource_id   [description]
     * @param  [type] $alertId       [description]
     * @return [type]                [description]
     */
    public static function getPostInteractions($resource_name,$resource_id,$alertId)
    {
        switch ($resource_name) {
            case 'Facebook Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return $model->topPostFacebookInterations;
                break;
            case 'Instagram Comments':
                $model = new \app\models\AlertsMencions();
                $model->alertId = $alertId;
                $model->resourcesId = $resource_id;
                
                return $model->topPostInstagramInterations;
                break;    
            
            default:
                # code...
                break;
        }
    }

    /**
     * [getProductInterations get interations from products]
     * @param  [type] $resourceName       [description]
     * @param  [type] $alerts_mention_ids [description]
     * @param  [type] $alertId            [description]
     * @return [type]                     [description]
     */
    public static function getProductInterations($resourceName,$alerts_mention_ids,$alertId)
    {
        $data = [];
        $models = \app\models\AlertsMencions::find()->where(['id' => $alerts_mention_ids,'alertId' => $alertId])->all();

        switch ($resourceName) {
            case 'Facebook Comments':
                // contadores
                $shares = 0;
                $likes = 0;
                $total = 0;
                foreach ($models as $model) {
                    $shares += $model->mention_data['shares'];
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        foreach ($model->mentions as $mention) {
                            $likes += $mention->mention_data['like_count'];
                        }
                    }
                }
                // shares
                $data['shares'] = $shares;
                //likes
                $data['likes'] = $likes;
                // total
                $data['total'] = $total;
                return $data;                
                break;
            
            case 'Facebook Messages':
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                    }
                }
                // total
                $data['total'] = $total;
                return $data;
                break;
            case 'Instagram Comments':
                $like_post = 0;
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        $like_post += $model->mention_data['like_count'];
                    }
                }
                // like post
                $data['like_post'] = $like_post;
                // total
                $data['total'] = $total;
                return $data; 
            case 'Twitter':
                $likes = 0;
                $retweets = 0;
                $total = 0;
                foreach ($models as $model) {
                    if($model->mentionsCount){
                        $total += $model->mentionsCount;
                        foreach ($model->mentions as $mention) {
                            $likes += $mention->mention_data['favorite_count'];
                            $retweets += $mention->mention_data['retweet_count'];
                        }

                    }
                }
                // count values in document
                $alertsMencions = new \app\models\AlertsMencions();
                $alertMentionsDocuments = \app\models\AlertsMencions::find()->where(['alertId' => $alertId,'type' => 'document'])->all();
                foreach ($alertMentionsDocuments as $alertMentionsDocument) {
                    if($alertMentionsDocument->mentionsCount){
                        $total += $alertsMencions->getCountDocumentByResource('TWITTER',$alertMentionsDocument->id);
                    }
                }
                // set
                $data['total'] = $total;
                $data['likes_twitter'] = $likes;
                $data['retweets'] = $retweets;
                return $data;
                break;
            case 'Live Chat':
                $total = 0;
                $expression = new Expression("`mention_data`->'$.id' AS ticketId");
                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('ticketId')
                      ->count();
                    $total += intval($rows);  
                    
                }
                // set
                $data['total'] = $total;
                return $data; 
                break;

            case 'Live Chat Conversations':
                $total = 0;
                $expression = new \yii\db\Expression("`mention_data`->'$.event_id' AS event_id");
                foreach ($models as $model) {
                    $rows = (new \yii\db\Query())
                      ->select($expression)
                      ->from('mentions')
                      ->where(['alert_mentionId' => $model->id])
                      ->groupBy('event_id')
                      ->count();
                    $total += intval($rows);  
                }
                // set
                $data['total'] = $total;
                return $data; 

                break;
            case 'Excel Document':
                $total = 0;
                foreach ($models as $model) {
                    if ($model->mentionsCount) {
                        $total += $model->mentionsCount;
                    }
                }
                // set
                $data['total'] = $total;
                return $data; 
                break;         

            default:
                # code...
                return '1';
                break;
        }
    }

    /**
     * [getProductByTermSearch return model product or nul if not exits]
     * @param  [type] $term_searched [description]
     * @return [obj/ null]                [description]
     */
    public static function getProductByTermSearch($term_searched)
    {
        $is_model = \app\models\ProductsModels::find()->where(['name' => $term_searched])->exists();
        $model = [];

        if($is_model){
            $product_model = \app\models\ProductsModels::findOne(['name' => $term_searched]);
            $model = \app\models\Products::findOne($product_model->productId);
        }else{
            $model = \app\models\Products::findOne(['name' => $term_searched]);
        }

        return $model;

    }

}