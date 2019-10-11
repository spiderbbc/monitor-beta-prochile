<?php

namespace app\modules\monitor\controllers;

use Facebook\Facebook;

class FacebookController extends \yii\web\Controller
{
    public function actionIndex()
    {
      return $this->render('index');
    }
    /**
     * Login with facebook and save the access_secret_token.
     * @return view the loaded home
     */
    public function actionLogin()
    {
        $fb = \app\helpers\FacebookHelper::getFacebook();

        $helper = $fb->getRedirectLoginHelper();

        try {
          $accessToken = $helper->getAccessToken();
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
          // When Graph returns an error
          echo 'Graph returned an error: ' . $e->getMessage();
          exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
          // When validation fails or other local issues
          echo 'Facebook SDK returned an error: ' . $e->getMessage();
          exit;
        }

        if (! isset($accessToken)) {
          if ($helper->getError()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $helper->getError() . "\n";
            echo "Error Code: " . $helper->getErrorCode() . "\n";
            echo "Error Reason: " . $helper->getErrorReason() . "\n";
            echo "Error Description: " . $helper->getErrorDescription() . "\n";
          } else {
            header('HTTP/1.0 400 Bad Request');
            echo 'Bad request';
          }
          exit;
        }

        // The OAuth 2.0 client handler helps us manage access tokens
        $oAuth2Client = $fb->getOAuth2Client();

        // Get the access token metadata from /debug_token
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);

        // Validation (these will throw FacebookSDKException's when they fail)
        $tokenMetadata->validateAppId(\app\helpers\FacebookHelper::$_app_id); // Replace {app-id} with your app id
        // If you know the user ID this access token belongs to, you can validate it here
        //$tokenMetadata->validateUserId('123');
       


        if (! $accessToken->isLongLived()) {
          // Exchanges a short-lived access token for a long-lived one
          try {
            $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
          } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
            exit;
          }
        }
        

        $userId = \Yii::$app->user->id;
        $access_secret_token = $accessToken->getValue();
        $expiresAt_secret_token = $accessToken->getExpiresAt()->getTimestamp();
      

        if(!\app\helpers\FacebookHelper::saveAccessToken($userId,$accessToken)){
          \Yii::$app->session->setFlash('error', 'Cannot save accessToken');
        }

        if(!\app\helpers\FacebookHelper::saveExpiresAt($userId,$expiresAt_secret_token)){
          \Yii::$app->session->setFlash('error', 'Cannot save expiresAt secret token');
        }
        
        return $this->goHome();
    }
    /**
     * Finds the CredencialsApi by user and delete row.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $credencials_api_id
     * @return view the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionLogout($credencials_api_id)
    {
       $model = $this->findModel($credencials_api_id);
      // $model->delete(); 
       $url = 'http://localhost/monitor-beta/web/monitor/facebook/login'; 

       $linkLogout = \app\helpers\FacebookHelper::logout($model->access_secret_token,$url);
       
      // \Yii::$app->user->logout();
        
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
