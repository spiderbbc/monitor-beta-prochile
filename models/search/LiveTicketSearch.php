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
     * @return boolean status
     */
    public function search()
    {   
        // if doesnt dictionaries and doesnt boolean
        if(!$this->isDictionaries && !$this->isBoolean){
             //echo "save data .. \n";
            // save all data
            $tickets = $this->data;
            $search = $this->saveTickets($tickets);
            return $search;
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
            //echo "only dictionaries \n";
            $data = $this->data;
            $filter_data = $this->searchDataByDictionary($data);
            $search = $this->saveTickets($filter_data);
            return $search;
            
        }

        // if  !dictionaries and  boolean
        if(!$this->isDictionaries && $this->isBoolean){
            // init search
            echo "only boolean \n";
            // retur something
        }

    }
    /**
     * [saveTickets save the tickets]
     * @param  [array] $data [tickets]
     * @return [bool]       [true if not a problem]
     */
    private function saveTickets($data){
        $error = [];

        foreach($data as $product => $tickets){
            $alertsMencionsModel = $this->findAlertsMencions($product);
            if(!is_null($alertsMencionsModel)){
                for($t = 0 ; $t < sizeof($tickets); $t ++){
                   
                    $idTicket = $tickets[$t]['id'];
                    $requesterIp = $tickets[$t]['requester']['ip'];
                    $geolocation = \app\helpers\MentionsHelper::getGeolocation($requesterIp);
                    $requesterSource = $tickets[$t]['source'];

                    if(ArrayHelper::keyExists('events', $tickets[$t], false)){
                        for($w = 0 ; $w < sizeOf($tickets[$t]['events']); $w++){
                            if(ArrayHelper::keyExists('message', $tickets[$t]['events'][$w], false)){
                                if($tickets[$t]['events'][$w]['author']['type'] == 'client'){
                                    $tickets[$t]['events'][$w]['author']['ip'] = $requesterIp;
                                    $tickets[$t]['events'][$w]['author']['geolocation'] = $geolocation;
                                } // if client insert his ip
                                $user = $this->saveUserMencions($tickets[$t]['events'][$w]['author']);
                                
                                if(empty($user->errors)){
                                    // adding informacion to tickets array
                                    $tickets[$t]['events'][$w]['id']          = $idTicket;
                                    $tickets[$t]['events'][$w]['source']      = $requesterSource;
                                    $tickets[$t]['events'][$w]['rate']        = $tickets[$t]['rate'];
                                    $tickets[$t]['events'][$w]['status']      = $tickets[$t]['status'];
                                    $tickets[$t]['events'][$w]['subject']     = $tickets[$t]['subject'];
                                   // $tickets[$t]['events'][$w]['geolocation'] = $geolocation;

                                    $mention = $this->saveMentions($tickets[$t]['events'][$w],$alertsMencionsModel->id,$user);

                                    if(empty($mention->errors)){
                                        if(ArrayHelper::keyExists('wordsId', $tickets[$t]['events'][$w], false)){
                                            $keywordsMention = $this->saveKeywordsMentions($tickets[$t]['events'][$w]['wordsId'],$mention->id);
                                        }

                                    }else{$errors['mentions'][] = 'mentions Faild!!';}// end if mention erros

                                }else{ $errors['user_mentions'][] = 'user Faild!!';}// end if errors
                            }// end fi message
                        }// end for events
                    } // if array keyExists
                }// end for tickets
            }
        } // end for  each data
        
        return (!count($error)) ? true : false;
    }

    /**
     * [saveUserMencions save or return user in table user_mentions]
     * @param  [array] $author [part of array the tickets]
     * @return [model]         [model instance table user_mentions]
     */
    private function saveUserMencions($author){

        $where = [
           // 'name' => $author['name'],
            'screen_name' => $author['id']
        ];

        $user_data['type'] = $author['type'];
        
        if(isset($author['geolocation'])){
            $user_data['geo']    = $author['geolocation'];
        }        
        if(isset($author['ip'])){
            $user_data['ip'] = $author['ip'];
        }

        

        $model = \app\helpers\MentionsHelper::saveUserMencions(
            [
                'screen_name' => $author['id'],

            ],
            [
                'screen_name' => $author['id'],
                'name'        => $author['name'],
                'user_data'   => $user_data,
            ]
        );


        return $model;
    }
    /**
     * [saveMentions save mentions]
     * @param  [type] $mention          [mention]
     * @param  [type] $alertsMencionsId [description]
     * @param  [type] $user             [description]
     * @return [type]                   [description]
     */
    private function saveMentions($mention,$alertsMencionsId,$user){
       
        $date = \app\helpers\DateHelper::asTimestamp($mention['date']);
        $mention_data = [];
        $mention_data['id']     = $mention['id'];
        $mention_data['status'] = $mention['status'];
        //$mention_data['geo']    = ($user->user_data['type'] == 'client') ? $mention['geolocation']: null;
        $mention_data['source'] = $mention['source']['type'];

        $subject        = $mention['subject'];
        $message        = $mention['message'];
        $message_markup = $mention['message_markup'];
        $url            = ($user->user_data['type'] == 'client') ? $mention['source']['url'] : null;
      //  $location       = ($user->user_data['type'] == 'client') ? $mention['geolocation']['regionName'] : null;
        $domain_url     = ($user->user_data['type'] == 'client') ? \app\helpers\StringHelper::getDomain($mention['source']['url']) : null;


        $where = [
            'created_time'    => $date,
            'message'         => $message,
            'origin_id'       => $user->id,
            'alert_mentionId' => $alertsMencionsId,
        ];

        $isMentions = \app\models\Mentions::find()->where($where)->exists();

        if($isMentions){
            $model = \app\models\Mentions::find()->where($where)->one();

        }else{
            
            $model = new \app\models\Mentions();
            
            $model->alert_mentionId = $alertsMencionsId;
            $model->origin_id       = $user->id;
            $model->created_time    = $date;
            $model->mention_data    = $mention_data;
            $model->subject         = $subject;
            $model->message         = $message;
            $model->message_markup  = $message_markup;
            $model->url             = $url;
            $model->domain_url      = $domain_url;
         //   $model->location        = $location;
            $model->social_id       = null;
            
            $model->save();
        }
        return $model;


    }
    /**
     * [saveKeywordsMentions save keywords id on table pivote]
     * @param  [array] $wordsId   [id words and each repate in the sentence]
     * @param  [type] $mentionId [id of the mentionId]
     * @return [type]            [description]
     */
    private function saveKeywordsMentions($wordsId,$mentionId){

        foreach($wordsId as $idwords => $count){
            if(!\app\models\KeywordsMentions::find()->where(['mentionId'=> $mentionId,'keywordId' => $idwords])->exists()){
                for($c = 0; $c < $count; $c++){
                    $model = new \app\models\KeywordsMentions();
                    $model->keywordId = $idwords;
                    $model->mentionId = $mentionId;
                    $model->save();
                }
            }
            
        }

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
                                $word = " {$words[$w]['name']} ";
                                $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
                                if($containsCount){
                                    $wordsId[$words[$w]['id']] = $containsCount;
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
           // 'condition'     =>  'ACTIVE',
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