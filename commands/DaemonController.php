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
        var_dump($alertsConfig);
        

    }


    public function twitterApi($alert){
       return ExitCode::OK;
    }


    public function livechatConversations($alert){
        return ExitCode::OK;
    }
}
