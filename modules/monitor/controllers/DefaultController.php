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
    	$msg = "Dani Castro nos enseña a preparar unos ricos macarrones con queso en el microondas LG NeoChef 🙌🏼. \nMira el video y descubre los pasos para preparar esta exquisita receta 😍.\n\nMás sobre nuestro microondas en 👉🏻\nhttp://spr.ly/6189EtaQU";

    	$data = \app\helpers\StringHelper::structure_product_to_search('LG G6 (32 GB / Astro Black)');
    	var_dump($data);
    	$product_data = \app\helpers\StringHelper::containsAny($msg, $data);
    	var_dump($product_data);
    	die();
        return $this->render('index');
    }
}
