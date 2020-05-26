<?php

namespace app\modules\monitor\controllers;

use yii\web\Controller;



/**
 * Default controller for the `monitor` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
		$this->render('index');
    }
}
