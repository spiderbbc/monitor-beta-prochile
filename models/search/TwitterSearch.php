<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;

/**
 * TwitterSearch represents the model behind the search form of `app\models\api\TwitterApi`.
 */
class TwitterSearch 
{
    public $alertId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;

    public function load($params){
        if(empty($params)){
           return false;     
        }
        $this->alertId = ArrayHelper::getValue($params, 0);
        $this->isDictionaries = $this->_isDictionaries();
        // is boolean
        
        // loop data
        for($p = 1; $p < sizeof($params); $p++){
            // loop with json file
            for($j = 0; $j < sizeof($params[$p]); $j++){
                $products = $params[$p][$j][0];
                // for each product
                foreach($products as $product => $datos){
                   // for each tweets 
                   for($d = 0; $d < sizeof($datos); $d++){
                        if(!ArrayHelper::keyExists($product, $this->data, false)){
                            $this->data[$product] = [];
                        }
                        if(!in_array($datos[$d], $this->data[$product])){
                            $this->data[$product] [] = $datos[$d];
                        }
                   }// en foreach tweets
                }// end for  each product
            } // end loop json
        }
        return true;
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
        ];
    }


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
            // save all data
            $this->saveData();
            // retur something
        }

        // if  dictionaries and  boolean
        if($this->isDictionaries && $this->isBoolean){
            // init search
            echo "boolean and dictionaries \n";
            // retur something
        }

        // if  dictionaries and  !boolean
        if($this->isDictionaries && !$this->isBoolean){
            // init search
            echo "only dictionaries \n";
            // retur something
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }

    private function _isDictionaries(){
        if(!is_null($this->alertId)){
            $keywords = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->exists();
            return $keywords;
        }
        return false;
    }

    private function saveData(){

        if(!is_null($this->data)){
            foreach($this->data as $product => $tweets){
                $alertsMencions =  \app\models\AlertsMencions::find()->where([
                    'type'          =>  'tweet',
                    'condition'    =>  'ACTIVE',
                    'term_searched' =>  $product,
                    'alertId'       => $this->alertId
                ])->asArray()->one();

                for($t = 0; $t < sizeof($tweets); $t++){
                    
                    $user_data['followers_count'] = $tweets[$t]['user']['followers_count'];
                    $user_data['friends_count'] = $tweets[$t]['user']['friends_count'];
                    $author = \app\helpers\StringHelper::remove_emoji($tweets[$t]['user']['author_name']);
                    $user = \app\helpers\MentionsHelper::saveUserMencions(
                        [
                            'user_uuid' => $tweets[$t]['user']['user_id']
                        ],
                        [
                            'name' => $author,
                            'screen_name' => $tweets[$t]['user']['author_username'],
                            'user_data' => $user_data,
                        ]
                    );

                   //var_dump($user);
                }

            }
        }

        

    }

}
