<?php

namespace app\commands;

use yii\console\ExitCode;
use yii\console\Controller;
use Yii\helpers\ArrayHelper;
use yii\helpers\Console;

use app\models\Alerts;
use app\models\api\BaseApi;
use app\models\api\DriveApi;

use yii\helpers\FileHelper;

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
        $this->stdout("runnig getBringAllAlertsToRun funtction.. \n", Console::BOLD);
        
        if(!empty($alertsConfig)){
           $baseApi = new BaseApi();
           $api = $baseApi->callResourcesApi($alertsConfig);
           $this->stdout("runnig callResourcesApi funtction baseApi.. \n", Console::BOLD);
        }
        
        return ExitCode::OK;
    }

    public function actionDataSearch(){
        $alert = new Alerts();
        $alertsConfig = $alert->getBringAllAlertsToRun();
        // look in the folder
        if(!empty($alertsConfig)){
            $folder = FileHelper::filterPath(\Yii::getAlias('@data'),['filter' => function($path){
                echo $path;
            }]);
            var_dump($folder);
        }
        $this->stdout("runnig actionDataSearch funtction.. \n", Console::BOLD);
    }

    public function actionSyncProducts(){
        $drive = new DriveApi();
        $drive->getContentDocument();
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
