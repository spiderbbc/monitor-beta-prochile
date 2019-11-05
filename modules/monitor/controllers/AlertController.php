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
                'only' => ['index','create'],
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
    public function actionChangePayStatus($id, $value)
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
        return $this->render('view', [
            'model' => $this->findModel($id),
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
            $error = true;
          }
          // config model
          $config->alertId = $alert->id;
          if($config->save()){
            //sources model
            $is_save_socialIds = $config->saveAlertconfigSources($alert->alertResourceId);
            if(!$is_save_socialIds){
              $error = true;
            }
          }else{ $error = true;}
          // keywords/ dictionaryIds model
          $dictionaryIds = Yii::$app->request->post('Alerts')['dictionaryIds'];
          if($dictionaryIds){
            \app\models\Dictionaries::saveDictionaryDrive($dictionaryIds,$alert->id);
          }
          // if free words is
          $free_words = Yii::$app->request->post('Alerts')['free_words'];
          if ($free_words){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_NAME;
            \app\models\Dictionaries::saveFreeWords($free_words,$alert->id,$dictionaryName);
          }
          // product_description
          if($config->product_description){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_PRODUCT;
            $words = explode(',', $config->product_description);
            \app\models\Dictionaries::saveFreeWords($words,$alert->id,$dictionaryName);
          }
          // tag competitors
          if($config->competitors){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_COMPETITION;
            $words = explode(',', $config->competitors);
            \app\models\Dictionaries::saveFreeWords($words,$alert->id,$dictionaryName);
          }
          // set product/models
          $products_models = Yii::$app->request->post('Alerts')['productsIds'];
          if($products_models){
            \app\models\Products::saveProductsModelAlerts($products_models,$alert->id);
          }
          // files
          if(\yii\web\UploadedFile::getInstance($alert, 'files')){
            // convert excel to array php
            $fileData = \app\helpers\DocumentHelper::excelToArray($alert,'files');
            // get resource document
            $resource = \app\models\Resources::findOne(['resourcesId' => 3]);
            // save in file json
            \app\helpers\DocumentHelper::saveJsonFile($alert->id,$resource->name,$fileData);
            // add resource document to the alert
            array_push($alert->alertResourceId,$resource->id);
            if(!$config->saveAlertconfigSources($alert->alertResourceId)){
                //sources model
                $error = true;
                $messages = $config->errors;
            }
          }

          
          // error to page view
          if($error){
            $alert->delete();
            return $this->render('error',[
              'name' => 'alert',
              'message' => 'Alers not created.'
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
        // count dictionaries for the update difference
        $countDictionaries = count($drive->dictionaries);
        //set date
        $config->start_date = DateHelper::asDatetime($config->start_date);
        $config->end_date = DateHelper::asDatetime($config->end_date);
        //free words
        $alert->free_words = $alert->freeKeywords;

        // set productIds
        $alert->productsIds  = $alert->products;
        // set tag 
        $config->product_description = explode(",",$config->product_description);
        $config->competitors = explode(",",$config->competitors);
        
        
        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post()) && $config->load(Yii::$app->request->post())) {
          $error = false;
          $messages;
          
          $alert->userId = 1;
          
          if(!$alert->save()){ 
            $messages = $alert->errors;
            $error = true;
          }
          // config model
          $config->alertId = $alert->id;
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
            array_push($alert->alertResourceId,$resource->id);
          }
          // set resource
          if(!$config->saveAlertconfigSources($alert->alertResourceId)){
              //sources model
              $error = true;
              $messages = $config->errors;
          }
          
          // keywords/ dictionaryIds model
          $dictionaryIds = Yii::$app->request->post('Alerts')['dictionaryIds'];
          if($dictionaryIds != ''){
            if(count($dictionaryIds) != $countDictionaries){
              \app\models\Dictionaries::saveDictionaryDrive($dictionaryIds,$alert->id);
            }
          }else{
              \app\models\Keywords::deleteAll(['alertId' => $alert->id]);
          } 
          

           // if free words is
          $free_words = Yii::$app->request->post('Alerts')['free_words'];
          if ($free_words){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_NAME;
            $dictionary = \app\models\Dictionaries::find()->where(['name' => $dictionaryName])->one();
            \app\models\Keywords::deleteAll([
                        'alertId' => $alert->id,
                        'dictionaryId' => $dictionary->id
            ]);
            \app\models\Dictionaries::saveFreeWords($free_words,$alert->id,$dictionaryName);
          }
          // if product_description
          if($config->product_description){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_PRODUCT;
            $dictionary = \app\models\Dictionaries::find()->where(['name' => $dictionaryName])->one();
            $words = explode(',', $config->product_description);

            \app\models\Keywords::deleteAll([
                        'alertId' => $alert->id,
                        'dictionaryId' => $dictionary->id
            ]);
            \app\models\Dictionaries::saveFreeWords($words,$alert->id,$dictionaryName);
          }

          // if competitors
          if($config->competitors){
            $dictionaryName = \app\models\Dictionaries::FREE_WORDS_COMPETITION;
            $dictionary = \app\models\Dictionaries::find()->where(['name' => $dictionaryName])->one();
            $words = explode(',', $config->competitors);
            \app\models\Keywords::deleteAll([
                        'alertId' => $alert->id,
                        'dictionaryId' => $dictionary->id
            ]);
            \app\models\Dictionaries::saveFreeWords($words,$alert->id,$dictionaryName);
          }

          // set product/models
          $products_models = Yii::$app->request->post('Alerts')['productsIds'];
          if($products_models){
            \app\models\ProductsModelsAlerts::deleteAll([
                'alertId' => $alert->id,
            ]);
            \app\models\Products::saveProductsModelAlerts($products_models,$alert->id);
          }
          // error to page view
          if($error){
            $alert->delete();
            $msg = [];
            foreach ($messages as $title => $message){
              $msg[$title] = $message;
            }
            return $this->render('error',[
                'name' => 'alert',
                'message' => $msg,
            ]);
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
        $this->findModel($id)->delete();
        // delete product models
        $ProductsModelsAlerts = \app\models\ProductsModelsAlerts::find()->where(['alertId' => $id])->all();
        foreach ($ProductsModelsAlerts as $productsModel){
          $productsModel->delete();
        }

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
