<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\console\Controller;
use Yii\helpers\ArrayHelper;
use yii\helpers\Console;

use app\models\Alerts;
use app\models\api\BaseApi;
use app\models\api\DriveApi;

use app\models\file\JsonFile;


/**
 * This command echoes the first argument that you have entered.
 *
 * This command will runs all the alerts.
 *
 * @author Eduardo Morales <eduardo@montana-studio.com>
 */
class DaemonController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        return ExitCode::OK;
    }
    /**
     * [actionAlertsRun runs all alerts]
     * @return [type] [description]
     */
    public function actionAlertsRun(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true);
        
        
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
        
        return ExitCode::OK;
    }

    /**
     * [actionAlertsRun runs all alerts when its resource are equal to web page]
     * @return [type] [description]
     */
    public function actionAlertsRunWeb(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun(true,'Paginas Webs');
        
        
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
        
        return ExitCode::OK;
    }

    
    /**
     * [actionDataSearch get json in transformed the data to db [Not finish]]
     * @return [type] [description]
     */
    public function actionDataSearch(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun();
        // look in the folder
        if(!empty($alertsConfig)){
            $baseApi = new BaseApi();
            $api = $baseApi->readDataResource($alertsConfig);
        }
    }

    /**
     * [actionInsightsRun call api to get insights]
     * @return [type] [description]
     */
    public function actionInsightsRun(){
        $userFacebook = \app\helpers\FacebookHelper::getUserActiveFacebook();
        if (!empty($userFacebook)) {
            $baseApi = new BaseApi();
            $api = $baseApi->callInsights($userFacebook);
        }
        return ExitCode::OK;
    }
    /**
     * [only development function]
     * @return [type] [description]
     */
    public function actionTruncateProducts(){
        \Yii::$app->db->createCommand()->delete('products_series','status = :status', [':status' => 1])->execute();
    }

}
