<?php 
namespace app\helpers;

use yii;
use app\models\HistorySearch;


/**
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 * @group  Montana-Studio LG 
 */

 /**
  * HistorySearchHelper wrapper for table db function.
  */
 class HistorySearchHelper
 {

    public static function createOrUpdate($alertId,$properties)
    {
        $is_model = HistorySearch::find()->where(['alertId' => $alertId])->exists();

        if($is_model){
            $model = HistorySearch::find()->where(['alertId' => $alertId])->one();
            
            $key = array_keys($properties);
            $tmp = $model->search_data;
            if(\yii\helpers\ArrayHelper::keyExists($key[0],$tmp)){
               
               $tmp[$key[0]]['status'] =  $properties[$key[0]]['status'];
               $model->search_data = $tmp;

            }else{
                
                $model->search_data = \yii\helpers\ArrayHelper::merge($tmp, $properties);
            }
            
            $model->save();
            
        }else{
            $model = new HistorySearch();
            $model->alertId = $alertId;
            $model->search_data = $properties;
            // save or update
            $model->save();
        }

    }
     
 }

?>