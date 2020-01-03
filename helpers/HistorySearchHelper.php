<?php 
namespace app\helpers;

use yii;
use app\models\HistorySearch;
use app\models\Alerts;


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
        $model = Alerts::findOne($alertId);

        $resourceIds = $model->config->configSourcesByAlertResource;

        if($is_model){
            $model = HistorySearch::find()->where(['alertId' => $alertId])->one();
            
            $key = array_keys($properties);
            $resource_search = $model->search_data;
            // update resource
            foreach ($resource_search as $resource => $value) {
               if(!in_array($value['resourceId'], $resourceIds)){
                   unset($resource_search[$resource]); 
               }
             } 

            ///
            
            if(\yii\helpers\ArrayHelper::keyExists($key[0],$resource_search)){
               
               $resource_search[$key[0]]['status'] =  $properties[$key[0]]['status'];
               $model->search_data = $resource_search;

            }else{
                
                $model->search_data = \yii\helpers\ArrayHelper::merge($resource_search, $properties);
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