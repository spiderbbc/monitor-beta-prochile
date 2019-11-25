<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;

/**
 * LiveTicketSearch represents the model behind the search form of `app\models\api\LiveTicketApi`.
 *
 */
class LiveTicketSearch {

	public $alertId;
    public $resourcesId;
    public $data = [];
    public $isDictionaries = false;
    public $isBoolean = false;


    /**
     * [load load in to local variables]
     * @param  [array] $params [product [feed]]
     * @return [boolean]
     */
    public function load($params){
        if(empty($params)){
           return false;     
        }
        $this->alertId        = ArrayHelper::getValue($params, 0);
        $this->resourcesId    = $this->_setResourceId();
        $this->isDictionaries = $this->_isDictionaries();

      
        for($p = 1 ; $p < sizeof($params); $p++){
            foreach($params[$p] as $data => $group){
                foreach($group as $pages => $products){
                    foreach($products as $product => $tickets){
                        //echo $product."\n";
                        if(!ArrayHelper::keyExists($product,$this->data)){
                            $this->data[$product] = [];
                        }// end if keyExists
                        for($t = 0 ; $t < sizeOf($tickets); $t++){
                            if(!in_array($tickets[$t], $this->data[$product])){
                                $this->data[$product][] = $tickets[$t];
                            }// end if in_array
                        }// end loop tickets
                    }
                }// end foreach products
            }// end foreach group
        }// end loop
        
        return (count($this->data)) ? true : false;
    }


     /**
     * methodh applied depends of type search
     *
     *
     * @return boolean status
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
             echo "save data .. \n";
            // save all data
            $tickets = $this->data;
           /* $search = $this->savetickets($tickets);
            return $search;*/
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
            $data = $this->data;
            $filter_data = $this->searchDataByDictionary($data);
            $search = $this->saveMentions($filter_data);
            /*
            return $search;*/
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }

    private function saveMentions($data){
        $error = [];

        foreach($data as $product => $tickets){
            $alertsMencionsModel = $this->findAlertsMencions($product);
            for($t = 0 ; $t < sizeof($tickets); $t ++){
                if(ArrayHelper::keyExists('events', $tickets[$t], false)){
                    for($w = 0 ; $w < sizeOf($tickets[$t]['events']); $w++){
                        if(ArrayHelper::keyExists('message', $tickets[$t]['events'][$w], false)){
                            $user = $this->saveUserMencions($tickets[$t]['events'][$w]['author']);

                        }// end fi message
                    }// end for events
                } // if array keyExists
            }// end for tickets
        } // end for  each data
    }


    private function saveUserMencions($author){
        $where = [
           // 'name' => $author['name'],
            'screen_name' => $author['id']
        ]; 
        $isUserExists = \app\models\UsersMentions::find()->where($where)->exists();

        if($isUserExists){
            $model = \app\models\UsersMentions::find()->where($where)->one();
        }else{
            $model = new \app\models\UsersMentions();
            $model->name = $author['name'];
            $model->screen_name = $author['id'];
            $user_data['type'] = $author['type'];
            $model->user_data = $user_data;
            $model->save(); 
        }

        var_dump($model->id);
    }


    /**
     * [searchDataByDictionary looking in data words in the dictionaries]
     * @param  [array] $data [description]
     * @return [array]       [description]
     */
    private function searchDataByDictionary($data){
        $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();
        
        foreach($data as $product => $tickets){
            for($t = 0; $t < sizeOf($tickets); $t ++){
                if(ArrayHelper::keyExists('events', $tickets[$t], false)){
                    for($e = 0 ; $e < sizeOf($tickets[$t]['events']); $e++){
                        if(ArrayHelper::keyExists('message', $tickets[$t]['events'][$e], false)){
                            $wordsId = [];
                            for($w = 0; $w < sizeof($words); $w++){
                                $sentence = $data[$product][$t]['events'][$e]['message_markup'];
                                $containsCount = \app\helpers\StringHelper::containsCount($sentence, $words[$w]['name']);
                                if($containsCount){
                                    $wordsId[$words[$w]['id']] = $containsCount;
                                    $word = $words[$w]['name'];
                                    $data[$product][$t]['events'][$e]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");

                                }// end if is containsCount

                            }// en for words
                            if(!empty($wordsId)){
                                $data[$product][$t]['events'][$e]['wordsId'] = $wordsId;
                            }
                        }// en if keyExists message
                    }// end for events
                }// end if key_exists
            } // enn loop tickets
        }// end foreach

        return $data;
    }


     

    /**
     * Finds the AlertsMencions model based on product key value.
     * @param string $product
     * @return AlertsMencions the loaded model
     */
    private function findAlertsMencions($product)
    {

        $alertsMencions =  \app\models\AlertsMencions::find()->where([
            'alertId'       => $this->alertId,
            'resourcesId'   =>  $this->resourcesId,
            'condition'     =>  'ACTIVE',
            'type'          =>  'ticket',
            'term_searched' =>  $product,
        ])
        ->select('id')->one();

        return $alertsMencions;

    } 

    /**
     * [_setResourceId return the id from resource]
     */
    private function _setResourceId(){
        
        $socialId = (new \yii\db\Query())
            ->select('id')
            ->from('type_resources')
            ->where(['name' => 'Social media'])
            ->one();
        
        
        $resourcesId = (new \yii\db\Query())
            ->select('id')
            ->from('resources')
            ->where(['name' => 'Live Chat','resourcesId' => $socialId['id']])
            ->all();
        

        return ArrayHelper::getColumn($resourcesId,'id')[0];

    }

    /**
     * [_isDictionaries is the alert hace dictionaries]
     * @return boolean [description]
     */
    private function _isDictionaries(){
        if(!is_null($this->alertId)){
            $keywords = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->exists();
            return $keywords;
        }
        return false;
    } 



}