<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\console\Controller;
use Yii\helpers\ArrayHelper;

use app\models\Alerts;
use app\models\api\BaseApi;

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

    public function actionAlert(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun();
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
        }
        
        return ExitCode::OK;
    }


    public function twitterApi($alert){
       return ExitCode::OK;
    }


    public function livechatConversations($alert){
        return ExitCode::OK;
    }
}
