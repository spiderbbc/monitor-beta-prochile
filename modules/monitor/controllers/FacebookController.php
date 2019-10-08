<?php

namespace app\modules\monitor\controllers;

use Facebook\Facebook;

class FacebookController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $fb = new Facebook([
          'app_id' => '446684435912359', // Replace {app-id} with your app id
          'app_secret' => '8eddd9257248c5cf03ded8cb5c82b2ca',
          'default_graph_version' => 'v3.2',
        ]);
        $helper = $fb->getRedirectLoginHelper();
       // return $this->render('index');
    }

    public function actionLogin()
    {
        return $this->render('login');
    }

    public function actionLogout()
    {
        return $this->render('logout');
    }

}
