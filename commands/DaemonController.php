<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

use app\models\Alerts;
use Yii\helpers\ArrayHelper;

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


        for($a = 0; $a < sizeOf($alertsConfig); $a++){
            if(!is_null($alertsConfig[$a]['resources'])){
                echo $alertsConfig[$a]['alert']['name'] . "\n";
                for($r = 0; $r < sizeOf($alertsConfig[$a]['resources']); $r++){
                    $resource = $alertsConfig[$a]['resources'][$r];
                    
                    switch($resource){
                        case "Twitter":
                            return $this->twitterApi($alertsConfig[$a]);
                            break;
                        case "Live Chat Conversations": 
                            return $this->livechatConversations($alertsConfig[$a]);
                            break;   
                    }
                }
            }
        }

    }


    public function twitterApi($alert){
       return ExitCode::OK;
    }


    public function livechatConversations($alert){
        return ExitCode::OK;
    }
}
