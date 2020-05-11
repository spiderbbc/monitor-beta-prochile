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
    	$data =[
    		[
    			'name' => 'your',
    			'data' => [
    				['date' => '2020-05-08','total' => '51'],
    			]
    		],
    		[
    			'name' => 'your',
    			'data' => [
    				['date' => '2020-05-08','total' => '102'],
    				['date' => '2020-05-07','total' => '1'],
    			]
    		],
    		[
    			'name' => 'year',
    			'data' => [
    				['date' => '2020-05-08','total' => '1'],
    			]
    		],

    	];

    	$static = [];
		$tmp_name = []; 
		for ($m=0; $m < sizeof($data) ; $m++) { 
			if (!in_array($data[$m]['name'], $tmp_name)) {
				$tmp_name[$m] = $data[$m]['name'];
			}else{
				$value = \yii\helpers\ArrayHelper::getValue($data[$m], 'data');
				$index = array_search($data[$m]['name'], $tmp_name);
				for ($v=0; $v < sizeof($value); $v++) { 
					$data[$index]['data'][] = $value[$v];
				}
				unset($data[$m]);
			}
			
		}

		$data = array_values($data);

    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['seriesWords' => $data];
    }
}
