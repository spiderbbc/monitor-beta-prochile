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
    	$period = \app\helpers\DateHelper::daysUntil('2020-04-09','2020-04-30');
    	echo "<pre>";
    	print_r($period);
    	die();
        return $this->render('index');
    }
}
