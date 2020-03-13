<?php

namespace app\models\search;

use yii\helpers\ArrayHelper;

/**
 * NewsSearch represents the model behind the search form of `app\models\api\NewsApi`.
 */
class NewsSearch 
{

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
                foreach($products as $product => $news){
                    //echo $product."\n";
                    if(!ArrayHelper::keyExists($product,$this->data)){
                        $this->data[$product] = [];
                    }// end if keyExists
                    for($n = 0 ; $n < sizeOf($news); $n++){
                        if(!in_array($news[$n], $this->data[$product])){
                            $this->data[$product][] = $news[$n];
                        }// end if in_array
                    }// end loop news
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
         //echo "save data .. \n";
        // save all data
        $news = $this->data;
        $search = $this->savenews($news);
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
        $search = $this->savenews($filter_data);
        return $search;
        
    }

    // if  !dictionaries and  boolean
    if(!$this->isDictionaries && $this->isBoolean){
        // init search
        echo "only boolean \n";
        // retur something
    }

  }

  public function savenews($data)
  {
    $error = [];
    foreach ($data as $product => $news) {
      $alertsMencionsModel = $this->findAlertsMencionsByProducts($product);
      if(!is_null($alertsMencionsModel)){
        for ($n=0; $n <sizeof($news) ; $n++) { 
          $authorName = $news[$n]['author'];
          if(!is_null($authorName)){
            $author = $this->saveUserMentions($authorName);

            if (empty($author->errors)) {
              $new = $news[$n];
              $mention = $this->saveMencions($new,$alertsMencionsModel->id,$author->id);
              if (empty($mention->errors)) {
                if($this->isDictionaries && ArrayHelper::keyExists('wordsId', $news[$n], false)){
                  $wordIds = $news[$n]['wordsId'];
                  // save Keywords Mentions 
                  $this->saveKeywordsMentions($wordIds,$mention->id);
                }// end if isDictionaries
              }else{
                $author->delete();
                $error['mention'] = $mention->errors;
              }// end mention error
            }else{
              $error['user'] = $author->errors;
            }// end error mention// end if author
          }// end if is_null author name
        } // end loop news
      }// end if !null
    }// end foreach $data
    return (empty($error)) ? true : false;
  }
	/**
  * Finds the AlertsMencions model based on product key value.
  * @param string $product
  * @return AlertsMencions the loaded model
  */
  private function findAlertsMencionsByProducts($product)
  {
    $alertsMencions =  \app\models\AlertsMencions::find()->where([
        'alertId'       =>  $this->alertId,
        'resourcesId'   =>  $this->resourcesId,
        'type'          =>  'web',
        'term_searched' =>  $product,
    ])->select('id')->one();


    return $alertsMencions;
  }

  /**
  * [saveUserMentions save the user if is not in db or return is found it]
  * @param  [array] $user [description]
  * @return [obj]       [user instance]
  */
  private function saveUserMentions($authorName){
    $authorName = \app\helpers\StringHelper::substring($authorName,0,62);
    $where = [
      'name' => $authorName,
      'screen_name' => $authorName
    ];

    $isUserExists = \app\models\UsersMentions::find()->where($where)->exists();

    if($isUserExists){
        $model = \app\models\UsersMentions::find()->where($where)->one();
    }else{
        $model = new \app\models\UsersMentions();
        $model->name = $authorName;
        $model->screen_name = $authorName;
        $model->save(); 
    }

    return $model;
  }

  /**
  * [saveMencions save or update mentions]
  * @param  [array] $tweets           [tweet]
  * @param  [int] $alertsMencionsId   [alert mentions]
  * @param  [int] $originId           [id user ]
  * @return [obj]                     [model mentions]
  */
  private function saveMencions($new,$alertsMencionsId,$originId){

    $created_time = \app\helpers\DateHelper::asTimestamp($new['publishedAt']);
    $subject = (!empty($new['title'])) ? $new['title'] : $new['description'];
    $message = $new['content'];
    $message_markup = $new['message_markup'];
    $url = $new['url'];
    $domain_url = \app\helpers\StringHelper::getDomain($new['url']);
    
    $new['source']['urlToImage'] =  $new['urlToImage'];
    $mention_data['source'] = $new['source'];

    $mention = \app\helpers\MentionsHelper::saveMencions(
        [
            'alert_mentionId'     => $alertsMencionsId,
            'origin_id'           => $originId ,
            //'created_time'        => $created_time,
            'subject'             => $subject,
        ],
        [
            'created_time'   => $created_time,
            'mention_data'   => $mention_data,
            'subject'        => $subject,
            'message'        => $message,
            'message_markup' => $message_markup,
            'url'            => $url ,
            'domain_url'     => $domain_url ,
            'location'       => '-' ,
            
        ]
    );

    return $mention;

  }

  /**
   * [searchDataByDictionary looking in data words in the dictionaries]
   * @param  [array] $data [description]
   * @return [array]       [description]
   */
  private function searchDataByDictionary($data){
    $words = \app\models\Keywords::find()->where(['alertId' => $this->alertId])->select(['name','id'])->asArray()->all();
    
    $model = [];  
    
    foreach($data as $product => $news){
        for($n = 0; $n < sizeOf($news); $n ++){
          $wordsId = [];
          for ($w=0; $w <sizeof($words) ; $w++) { 
            $sentence = $data[$product][$n]['message_markup'];
            $word = " {$words[$w]['name']} ";
            $containsCount = \app\helpers\StringHelper::containsCount($sentence, $word);
            if ($containsCount) {
              $wordsId[$words[$w]['id']] = $containsCount;
              $data[$product][$n]['message_markup']  = \app\helpers\StringHelper::replaceIncaseSensitive($sentence,$word,"<strong>{$word}</strong>");
            }
          }// end loop words
          if(!empty($wordsId)){
            if(!ArrayHelper::keyExists($product, $model)){
                  $model[$product] = [];
              }
              if(!in_array($news[$n], $model[$product])){
                  $news[$n]['wordsId'] = $wordsId;
                  $model[$product][] =  $news[$n];
              }
              //$data[$product][$n]['wordsId'] = $wordsId;
          }
        } // end loop news
    }// end foreach
    return $model;
  }
 
  /**
  * [saveKeywordsMentions save or update  KeywordsMentions]
  * @param  [array] $wordIds   [array wordId => total count in the sentece ]
  * @param  [int] $mentionId   [id mention]
  */
  private function saveKeywordsMentions($wordIds,$mentionId){

    if(\app\models\KeywordsMentions::find()->where(['mentionId'=> $mentionId])->exists()){
        \app\models\KeywordsMentions::deleteAll('mentionId = '.$mentionId);
    }

    foreach($wordIds as $idwords => $count){
        for($c = 0; $c < $count; $c++){
            $model = new \app\models\KeywordsMentions();
            $model->keywordId = $idwords;
            $model->mentionId = $mentionId;
            $model->save();
        }
    }

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

  /**
   * [_setResourceId return the id from resource]
   */
  private function _setResourceId(){
      
    $socialId = (new \yii\db\Query())
        ->select('id')
        ->from('type_resources')
        ->where(['name' => 'Web'])
        ->one();
    
    
    $resourcesId = (new \yii\db\Query())
        ->select('id')
        ->from('resources')
        ->where(['name' => 'Web page','resourcesId' => $socialId['id']])
        ->all();
    

    return ArrayHelper::getColumn($resourcesId,'id')[0];

  }
    
}