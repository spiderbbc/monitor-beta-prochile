<?php
namespace app\helpers;

use yii;

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


    public static function isAlertsMencionsExists($publication_id){
        if(\app\models\AlertsMencions::find()->where( [ 'publication_id' => $publication_id] )->exists()){
            return true;
        }
        return false;
    }
	
}