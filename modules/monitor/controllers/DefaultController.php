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
    	$msg = "¡Mantente en contacto sin hacer contacto! 🤯 Utiliza los comandos del LG G8s ThinQ para manejar tu smartphone sin tocar su pantalla. 🙌🏼 Descubrelo, aquí 👉🏻 http://lge.ai/61781bnaj";

    	$msg =  \app\helpers\StringHelper::substring($msg,0,80);
    	$msg =  \app\helpers\StringHelper::ensureRightPoints($msg);
    	echo $msg;
        //return $this->render('index');
    }
}
