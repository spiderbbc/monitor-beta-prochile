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
    	$test = "The Jewish";

        $str = "Elon Musk";
        $word_without_hash = \app\helpers\StringHelper::replace($str,"#"," ");
        $arr = preg_split('/(?=[A-Z])/',$word_without_hash);
        $sentence = trim(implode($arr," "));
        var_dump($sentence);
        //die();
    	/*$test = \app\helpers\StringHelper::dasherize($sentence);
    	$test = \app\helpers\StringHelper::replace($test,"-"," ");*/
        

        /*$word_without_hash = \app\helpers\StringHelper::replace($test,"#"," ");
        $word_dash = \app\helpers\StringHelper::dasherize($word_without_hash);
        $wordCamelCase = \app\helpers\StringHelper::replace($word_dash,"-"," ");
        $word = trim(\app\helpers\StringHelper::lowercase($wordCamelCase)); 
    	var_dump($word);*/
    	//die();

        $model = \app\modules\topic\models\MTopics::findOne(4);
        // get trendings
        if ($model->mWords) {
            $trends = [];
            foreach ($model->mWords as $word) {
                // replace "#" with space
                $word_without_hash = \app\helpers\StringHelper::replace($word->name,"#"," ");
                $arr = preg_split('/(?=[A-Z])/',$word_without_hash);
                $sentence = trim(implode($arr," "));
                 
                // convert to lower case
                $trends[$word->id] = $sentence;
            }
        }
        //echo "<pre>";
        print_r($trends);
        die();

        return $this->render('index');
    }
}
