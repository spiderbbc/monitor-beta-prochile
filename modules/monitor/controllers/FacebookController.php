<?php

namespace app\modules\monitor\controllers;

use Facebook\Facebook;

class FacebookController extends \yii\web\Controller
{
    public function actionIndex()
    {
      return $this->render('index');
    }
    

    public function actionValidateFb(){
      
      $fb = new \Facebook\Facebook([
        'app_id' => \Yii::$app->params['facebook']['app_id'],
        'app_secret' => \Yii::$app->params['facebook']['app_secret'],
        'default_graph_version' => 'v3.2',
      ]);

      $linkLogout = '';

      if (\Yii::$app->request->get('code')){

        try {
            $helper       = $fb->getRedirectLoginHelper();
            if (isset($_GET['state'])) {
              $helper->getPersistentDataHandler()->set('state', $_GET['state']);
            }
            $accessToken  = $helper->getAccessToken();
            $oAuth2Client = $fb->getOAuth2Client();

        }catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // There was an error communicating with Graph
            return $this->render('validate-fb', [
                'out' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>'
            ]);
        }

        if (isset($accessToken)) { // you got a valid facebook authorization token

            $userId = \Yii::$app->user->id;
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

            if(is_null($accessToken->getExpiresAt())){
              // if the fucking expired at not come
              $date = \app\helpers\DateHelper::add(time(),'90 day');
              $expiresAt_secret_token = \app\helpers\DateHelper::asTimestamp($date);
            }else{
              $expiresAt_secret_token = $accessToken->getExpiresAt()->getTimestamp();
            }
            

            if(!\app\helpers\FacebookHelper::saveAccessToken($userId,$accessToken)){
              \Yii::$app->session->setFlash('error', 'Cannot save accessToken');
            }

            if(!\app\helpers\FacebookHelper::saveExpiresAt($userId,$expiresAt_secret_token)){
              \Yii::$app->session->setFlash('error', 'Cannot save expiresAt secret token');
            }
           
            
            //return $this->goHome();
            return $this->redirect(['//monitor/alert']);


        } elseif ($helper->getError()) {
            // the user denied the request
            // You could log this data . . .
            return $this->render('validate-fb', [
                'out' => '<legend>Validation Log</legend><pre>' .
                '<b>Error:</b>' . print_r($helper->getError(), true) .
                '<b>Error Code:</b>' . print_r($helper->getErrorCode(), true) .
                '<b>Error Reason:</b>' . print_r($helper->getErrorReason(), true) .
                '<b>Error Description:</b>' . print_r($helper->getErrorDescription(), true) .
                '</pre>'
            ]);
        }
      }
      
      return $this->render('validate-fb', [
          'out' => '<div class="alert alert-warning"><h4>Oops! Nothing much to process here.</h4></div>'
      ]);
   
    }

    /**
     * Finds the CredencialsApi by user and delete row.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $credencials_api_id
     * @return view the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionLogout($userId)
    {
      $user_facebook = \app\models\CredencialsApi::find() ->where( [ 
        'userId' => $userId, 
        'resourceId' =>  2,
        'name_app' =>  \Yii::$app->params['facebook']['name_app'],
      ] )->one();

      $user_facebook->status = 0;
      $user_facebook->save();
      
      $link = \app\helpers\FacebookHelper::logoutLink($user_facebook);
      $linkLogout = '<a href="' . $link . '">Logout in with Facebook!</a>';

      
        
       return $this->render('logout',['linkLogout' => $linkLogout]);
    }

    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model =  \app\models\CredencialsApi::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

}
