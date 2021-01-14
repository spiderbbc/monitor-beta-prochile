<?php

namespace app\modules\monitor\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;


use app\helpers\DateHelper;

use app\models\Alerts;
use app\models\AlertsConfig;
use app\models\search\AlertSearch;

/**
 * AlertController implements the CRUD actions for Alerts model.
 */
class AlertController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index','create','view'],
                'rules' => [
                    [
                       // 'actions' => ['create'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['POST'],
                    'index' => ['GET', 'POST'],
                    
                ],
            ],
        ];
    }

     /**
     * @param $id
     * @param $value
     *
     * @return string
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionChangeStatus($id, $value)
    {
      $out = [];
      $model = $this->findModel($id);
      $model->status = $value;

      Yii::$app->response->format = 'json';
      if($model->save() && Yii::$app->request->isAjax)
      {
        $out['situation'] = "success";
        $out['title'] = Yii::t('app', $model->name);
        $out['text'] = Yii::t('app', 'El Status fue cambiado exitosamente.');
        
      }else{
        $out['situation'] = "error";
        $out['title'] = Yii::t('app', '¡Error!');
        $out['text'] = Yii::t('app',
            'Ha ocurrido un error al cambiar el estatus. Por favor inténtelo más tarde.');
      }

      return $out;

    }
    /**
     * [actionReloadProducts reload products to table products]
     * @return [type] [description]
     */
    public function actionReloadProducts(){
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      
      $drive = new \app\models\api\DriveApi();
      $drive->getContentDocument();

      return ['status'=>true];
    }

    public function actionGetCredentials($userId){
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $user = \app\models\Users::findOne($userId);
      $credential = $user->getCredencialsApis()->where(['name_app' => 'pro_chile_monitor'])->one();
      return ['credential'=>$credential];
    }
    /**
     * [actionDeleteResourceAlert delete resource for alert]
     * @param  [type] $alertId    [alertId from aler]
     * @param  [type] $resourceId [reosurce id]
     * @return [type]             [description]
     */
    public function actionDeleteResourceAlert($alertId,$resourceId)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $alert = $this->findModel($alertId);
      // delete resource in config-resource
      $configSource = \app\models\AlertconfigSources::findOne(['alertconfigId' => $alertId,'alertResourceId' => $resourceId]);
      if($configSource){
        $configSource->delete();
        // delete folder resourceName  
        \app\helpers\DirectoryHelper::removeDirectory($alert->id,$configSource->alertResource->name);
      }
      // delete mentions
      \app\models\AlertsMencions::deleteAll('alertId = :alertId AND resourcesId = :resourcesId', [':alertId' => $alertId, ':resourcesId' => $resourceId]);
      
      // delete user then no have mention
      Yii::$app->db
      ->createCommand(
          'DELETE FROM users_mentions WHERE users_mentions.id NOT IN ( SELECT distinct origin_id FROM mentions)'
      )
      ->execute();
  
      // delete document
      $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
      \yii\helpers\FileHelper::removeDirectory($folderPath);
      return ['status'=>true];
    }
    /**
     * [actionDeleteTermAlert delete term search form alert]
     * @param  [type] $alertId  [description]
     * @param  [type] $termName [description]
     * @return [type]           [description]
     */
    public function actionDeleteTermAlert($alertId,$termName)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $alert = $this->findModel($alertId);
      \app\models\TermsSearch::deleteAll('alertId = :alertId AND name = :name', [':alertId' => $alert->id,':name' => $termName]);
      // delete mentions
      \app\models\AlertsMencions::deleteAll('alertId = :alertId AND term_searched = :term_searched', [':alertId' => $alertId, ':term_searched' => $termName]);
      // delete document
      $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
      \yii\helpers\FileHelper::removeDirectory($folderPath);
      return ['status'=>true];
    }
    /**
     * [actionDeleteFilterAlert delete a type dictionary from query related with alert an restore file json]
     * @param  [type] $alertId        [id alert]
     * @param  [type] $dictionaryName [name dictionary]
     * @param  [type] $filterName     [type of word]
     * @return [type]                 [description]
     */
    public function actionDeleteFilterAlert($alertId,$dictionaryName,$filterName)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $alert = $this->findModel($alertId);
      $isDictionary = \app\modules\wordlists\models\Dictionaries::find()->where(['name' => $dictionaryName])->exists();
      // if dictionaryName is equal filterName is there a dictionary
      if ($dictionaryName == $filterName) {
        
        if ($isDictionary) {
          $dictionary = \app\modules\wordlists\models\Dictionaries::findOne(['name' => $dictionaryName]);
          $keywordsAlertExits = \app\modules\wordlists\models\Keywords::find()->where(['dictionaryId'=> $dictionary->id])->exists();
          if ($keywordsAlertExits) {
            $keywordsIds = \app\modules\wordlists\models\Keywords::find()->select('id')->where(['dictionaryId' => $dictionary->id])->all();
            $ids = \yii\helpers\ArrayHelper::getColumn($keywordsIds, 'id');  
            \app\modules\wordlists\models\AlertsKeywords::deleteAll([
                'alertId' => $alert->id,
                'keywordId' => $ids,
            ]);
          }
        }
      }else{
        // if not a dictionary is free keyword
        if ($isDictionary) {
           $dictionary = \app\modules\wordlists\models\Dictionaries::findOne(['name' => $dictionaryName]);
           $keywordsAlertExits = \app\modules\wordlists\models\Keywords::find()->where(['dictionaryId'=> $dictionary->id,'name' => $filterName])->exists();
           if ($keywordsAlertExits) {
            $keywordsIds = \app\modules\wordlists\models\Keywords::find()->select('id')->where(['dictionaryId'=> $dictionary->id,'name' => $filterName])->all();
            $ids = \yii\helpers\ArrayHelper::getColumn($keywordsIds, 'id');  
            \app\modules\wordlists\models\AlertsKeywords::deleteAll([
                'alertId' => $alert->id,
                'keywordId' => $ids,
            ]);
            \app\modules\wordlists\models\Keywords::deleteAll([
                'id' => $ids,
                'dictionaryId' => $dictionary->id,
                'name' => $filterName
            ]);
           }
        }

      }

      //move json file and delete mentions
      foreach ($alert->alertsMentions as $alertMention) {
        // move json file
        \app\helpers\DocumentHelper::moveFilesToRoot($alert->id,$alertMention->resources->name);
        // delete most repeated words
        \app\models\AlertsMencionsWords::deleteAll('alert_mentionId = :alert_mentionId', [':alert_mentionId' => $alertMention->id]);
      
        if ($alertMention->mentionsCount) {
          foreach ($alertMention->mentions as $mentions => $mention) {
            $mention->delete();
          }
        }
      }
      // delete document
      $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
      \yii\helpers\FileHelper::removeDirectory($folderPath);
      
      $status = ($isDictionary) ? true: false;
      return ['status'=>$status];
    }

    /**
     * [actionAddFilterAlert adding dictionaries or filter in update]
     * @param  [type] $alertId        [description]
     * @param  [type] $dictionaryName [description]
     * @param  [type] $filterName     [description]
     * @return [type]                 [description]
     */
    public function actionAddFilterAlert($alertId,$dictionaryName,$filterName)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $alert = $this->findModel($alertId);
      $alertMentionIds = [];
      //move json file and delete mentions
      foreach ($alert->alertsMentions as $alertMention) {
        // move json file
        \app\helpers\DocumentHelper::moveFilesToRoot($alert->id,$alertMention->resources->name);
        if ($alertMention->mentionsCount) {
            $alertMentionIds[] = $alertMention->id;
        }
      }
      \app\models\Mentions::deleteAll(['alert_mentionId' => $alertMentionIds]);
      return ['status' => true];
    }
    /**
     * [actionChangeLangAlert change languaje to alert ]
     * @param  [int] $alertId        [alert Id]
     * @param  [int] $lang [description]
     * @return [Array]                 [description]
     */
    public function actionChangeLangAlert($alertId,$lang)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
      $alert = $this->findModel($alertId);
      $status = null;
      if ($alert->config->lang != $lang) {
        $status = 'change';
        // delete mentions
        \app\models\AlertsMencions::deleteAll('alertId = :alertId', [':alertId' => $alertId]);
        foreach ($alert->config->sources as $source) {
          // delete folder resourceName  
          \app\helpers\DirectoryHelper::removeDirectory($alert->id,$source->name);
        }
        

      }

      return ['status' => $status];
    }

    /**
     * [actionDeleteUrlAlert delete url form the alert]
     * @param  [type] $alertId        [id alert]
     * @param  [type] $urlName [url to delete]
     * @return [type]                
     */
    public function actionDeleteUrlAlert($alertId,$urlName)
    {
      \Yii::$app->response->format = \yii\web\Response:: FORMAT_JSON;
     
      $scraping = new \app\models\api\Scraping();
      $resourceId = \app\helpers\AlertMentionsHelper::getResourceIdByName($scraping->resourceName);
      $type = $scraping::TYPE_MENTIONS;
      
      \app\models\AlertsMencions::deleteAll('alertId = :alertId  AND  resourcesId = :resourcesId AND type = :type AND url = :url', 
        [':alertId' => $alertId,':resourcesId' => $resourceId, ':type' => $type, ':url' => $urlName]);

      // delete document
      $folderPath = \Yii::getAlias("@runtime/export/{$alertId}/");
      \yii\helpers\FileHelper::removeDirectory($folderPath);  
      return ['status' => true];
    }
    /**
     * Lists all Alerts models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AlertSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Alerts model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
      $searchModel = new \app\models\search\MentionSearch();
      $dataProvider = $searchModel->search(\Yii::$app->request->queryParams,$id);

      return $this->render('view', [
        'model' => $this->findModel($id),
        'searchModel' => $searchModel,
        'dataProvider' => $dataProvider,
      ]);
    }

    /**
     * Creates a new Alerts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $alert   = new \app\models\Alerts();
        $config  = new \app\models\AlertConfig();
        $sources = new \app\models\AlertconfigSources();
        $drive   = new \app\models\api\DriveApi();

       $alert->scenario = 'saveOrUpdate';
        


        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post()) && $config->load(Yii::$app->request->post())) {

          $error = false;
          $alert->userId = Yii::$app->user->getId();
          // only test
          $alert->status = 1;


          if(!$alert->save()){ 
            $messages = $alert->errors;
            $error = true;
          }
          // config model
          $config->alertId = $alert->id;
          $config->start_date = Yii::$app->request->post('start_date');
          $config->end_date = Yii::$app->request->post('end_date');
          // accounts
          $config->product_description = (empty(Yii::$app->request->post('AlertConfig')['product_description'])) ? Yii::$app->params['facebook']['business_id'] : Yii::$app->request->post('AlertConfig')['product_description'];
          //languaje
          $config->lang = (empty(Yii::$app->request->post('AlertConfig')['lang']))? 0 : Yii::$app->request->post('AlertConfig')['lang'];

          $config->urls = (Yii::$app->request->post('AlertConfig')['urls']) ?
                          implode(',', Yii::$app->request->post('AlertConfig')['urls']) : null;

          if($config->save()){
            //sources model
            $is_save_socialIds = $config->saveAlertconfigSources($alert->alertResourceId);

            if(!$is_save_socialIds){
              $error = true;
              $messages = $config->errors;
            }
          }else{ 
            $messages = $config->errors;
            $error = true;
          }
          // keywords/ dictionaryIds model
          $dictionaryIds = Yii::$app->request->post('Alerts')['dictionaryIds'];
          if ($dictionaryIds) {
            \app\modules\wordlists\models\Dictionaries::saveDictionary(
                $dictionaryIds,
                $alert->id
            );
        }
          // if free words is
          $free_words = Yii::$app->request->post('Alerts')['free_words'];
          if ($free_words){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_NAME;
            \app\models\Dictionaries::saveFreeWords($free_words,$alert->id,$dictionaryName);
          }
          
          // set product/models
          $products_models = Yii::$app->request->post('Alerts')['productsIds'];
          if($products_models){
            //\app\models\Products::saveProductsModelAlerts($products_models,$alert->id);

            foreach ($products_models as $key => $term) {
              $model_term = new \app\models\TermsSearch();
              $model_term->alertId = $alert->id;
              $model_term->name = $term;
              $model_term->save();
            }
          }
          // files
          if(\yii\web\UploadedFile::getInstance($alert, 'files')){
            // convert excel to array php
            $fileData = \app\helpers\DocumentHelper::excelToArray($alert,'files');
            // get resource document
            $resource = \app\models\Resources::findOne(['resourcesId' => 3]);
            // save in file json
            \app\helpers\DocumentHelper::saveJsonFile($alert->id,$resource->name,$fileData);
          }

          
          // error to page view
          if($error){
            $alert->delete();
            return $this->render('error',[
              'name' => 'alert',
              'message' => $messages,

            ]);
          }
           
          Yii::$app->getSession()->setFlash('success', 'Alers has been created.');
          return $this->redirect(['view', 'id' => $alert->id]);
        } 

        return $this->render('create', [
            'alert'   => $alert,
            'config'  => $config,
            'drive'   => $drive,
        ]);
    }

    /**
     * Updates an existing Alerts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
       
        $alert = $this->findModel($id);
        $config = $alert->config;
        
        $drive   = new \app\models\api\DriveApi();
        
        //set date
        $config->start_date = DateHelper::asDatetime($config->start_date);
        $config->end_date = DateHelper::asDatetime($config->end_date);
        
        //free words
        $alert->free_words = $alert->freeKeywords;
        
        // set productIds
        //$alert->productsIds  = $alert->products;
       
        $alert->productsIds  = \yii\helpers\ArrayHelper::map(\app\models\TermsSearch::find()->where(['alertId' => $alert->id])->all(),'id','name');
       
        // set tag 
        //$config->product_description = explode(",",$config->product_description);
        $config->competitors = explode(",",$config->competitors);
        // covert reade
        $config->urls = explode(",",$config->urls);

        $alert->scenario = 'saveOrUpdate';
        // change inactive while is update
        $alert->status = 0;
        $alert->save();

        $isDocumentExist = \app\helpers\DocumentHelper::isDocumentExist($alert->id,'Excel Document');

        // reset alerts_mentions
        if (Yii::$app->getRequest()->getQueryParam('fresh') == 'true') {
          $alerts_mentions = \app\models\AlertsMencions::deleteAll('alertId = :alertId', [':alertId' => $id]);
          $alert->status = 0;
          $alert->save();
          // delete history
          \app\helpers\HistorySearchHelper::deleteHistory($alert->id);
        }

        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post()) && $config->load(Yii::$app->request->post())) {
          
          $error = false;
          $messages;
          
          $alert->userId = Yii::$app->user->getId();
          $alert->status = 1;
          
          if(!$alert->save()){ 
            $messages = $alert->errors;
            $error = true;
          }
          // config model
          $config->alertId = $alert->id;
          $config->start_date = Yii::$app->request->post('start_date');
          $config->end_date = Yii::$app->request->post('end_date');
          //languaje
          $config->lang = Yii::$app->request->post('AlertConfig')['lang'];
          $config->urls = (Yii::$app->request->post('AlertConfig')['urls']) ?
                          implode(',', Yii::$app->request->post('AlertConfig')['urls']) : null;

          // accounts
          $config->product_description = (empty(Yii::$app->request->post('AlertConfig')['product_description'])) ? Yii::$app->params['facebook']['business_id'] : Yii::$app->request->post('AlertConfig')['product_description'];                          
          $config->save();

          // add resource alert
          $alert->alertResourceId = Yii::$app->request->post('Alerts')['alertResourceId'];
          // files
          if(\yii\web\UploadedFile::getInstance($alert, 'files')){

            // convert excel to array php
            $fileData = \app\helpers\DocumentHelper::excelToArray($alert,'files');
            // get resource document
            $resource = \app\models\Resources::findOne(['resourcesId' => 3]);
            // save in file json
            \app\helpers\DocumentHelper::saveJsonFile($alert->id,$resource->name,$fileData);
            // add resource document to the alert
            //array_push($alert->alertResourceId,$resource->id);
            
          }else{
            if($isDocumentExist){
              // get resource document
              $resource = \app\models\Resources::findOne(['resourcesId' => 3]);
              // add resource document to the alert
              array_push($alert->alertResourceId,$resource->id);
            }
          }
          // set resource
          if(!$config->saveAlertconfigSources($alert->alertResourceId)){
              //sources model
              $error = true;
              $messages = $config->errors;
          }
          
          // keywords/ dictionaryIds model
          $dictionaryIds = Yii::$app->request->post('Alerts')['dictionaryIds'];

          \app\modules\wordlists\models\Dictionaries::updateDictionaries(
              $dictionaryIds,
              $alert->id
          );

           // if free words is
           $free_words = Yii::$app->request->post('Alerts')['free_words'];
           $dictionaryName = \app\modules\wordlists\models\Dictionaries::FREE_WORDS_NAME;
           $dictionary = \app\modules\wordlists\models\Dictionaries::find()
               ->where(['name' => $dictionaryName])
               ->one();
           if ($free_words) {
               \app\modules\wordlists\models\Dictionaries::saveOrUpdateWords(
                   $free_words,
                   $alert->id,
                   $dictionary->id
               );
           } else {
               $keywordsIds = \app\modules\wordlists\models\Keywords::find()->select('id')->where(['dictionaryId' => $dictionary->id])->all();
               $ids = \yii\helpers\ArrayHelper::getColumn($keywordsIds, 'id');
               \app\modules\wordlists\models\AlertsKeywords::deleteAll([
                   'alertId' => $alert->id,
                   'keywordId' => $ids,
               ]);
               \app\modules\wordlists\models\Keywords::deleteAll([
                   'id' => $ids,
                   'dictionaryId' => $dictionary->id,
               ]);
           }


          // set product/models
          $products_models = Yii::$app->request->post('Alerts')['productsIds'];
          if($products_models){
            \app\models\TermsSearch::deleteAll([
                'alertId' => $alert->id,
            ]);
            //\app\models\Products::saveProductsModelAlerts($products_models,$alert->id);
            foreach ($products_models as $key => $term) {
              $model_term = new \app\models\TermsSearch();
              $model_term->alertId = $alert->id;
              $model_term->name = $term;
              $model_term->save();
            }
          }
          // error to page view
          if($error){
            $alert->delete();
            return $this->render('error',[
                'name' => 'alert',
                'message' => $messages,
            ]);
          }
          // delete history
         \app\helpers\HistorySearchHelper::deleteHistory($alert->id);
         //restore file json
         //move json file and delete mentions
         foreach ($alert->alertsMentions as $alertMention) {
            // move json file
            \app\helpers\DocumentHelper::moveFilesToRoot($alert->id,$alertMention->resources->name);
          }
         
          // return view
          return $this->redirect(['view', 'id' => $alert->id]);
        }

        return $this->render('update', [
            'alert' => $alert,
            'drive' => $drive,
            'config' => $config,
        ]);
    }

    /**
     * Deletes an existing Alerts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $alert_delete = Yii::$app->db->createCommand('DELETE FROM alerts WHERE id=:alertId');
        
        // delete history search
        $history_search = \app\models\HistorySearch::findOne(['alertId' => $model->id]);
        if($history_search){
          // delete history
          $history_search->delete();
        }
        // remove directory
        \app\helpers\DirectoryHelper::removeDirectory($id);
        // prepare and execute delete alert
        $alert_delete->bindParam(':alertId', $id);
        $alert_delete->execute();
        // delete user then no have mention
        Yii::$app->db->createCommand('DELETE FROM users_mentions WHERE users_mentions.id NOT IN ( SELECT distinct origin_id FROM mentions)')->execute(); 

        return $this->redirect(['index']);
    }
    /**
     * Finds the Alerts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alerts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Alerts::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
