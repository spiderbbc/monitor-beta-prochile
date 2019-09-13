<?php

namespace app\modules\monitor\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;


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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
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

        

        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post()) && $config->load(Yii::$app->request->post())) {
            $error = false;

            $alert->userId = 1;
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
              $model = new \app\models\Dictionaries();
              $dictionaries = $model->getOrSaveDictionary($dictionaryIds);
              if($dictionaries){
                foreach ($dictionaries as $dictionaryId => $dictionaryName){
                  $keywords_drive = $drive->getContentDictionaryByTitle([$dictionaryName]);
                  foreach ($keywords_drive[$dictionaryName] as $word) {
                    $models[] = [$alert->id,$dictionaryId,$word];
                }
                    
              }
              Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
                          ->execute();
              }
            }
            // if free words is
            $free_words = Yii::$app->request->post('Alerts')['free_words'];
            $model = []; 
            if ($free_words){
              foreach ($free_words as $word){
                $models[] = [$alert->id,4,$word];
              }
              // save free words 
              Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
              ->execute();
            }
            // set product/models
            $products_models = Yii::$app->request->post('Alerts')['productsIds'];
            $model = new \app\models\Products();
            $productsIds = $model::getModelsIdByName($products_models);
            
            foreach ($productsIds as $id => $name) {
                $model = new \app\models\ProductsModelsAlerts();
                $model->alertId = $alert->id;
                $model->product_modelId = $id;
                if(!$model->save()){
                  $error = true;
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
            'sources' => $sources,
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
        $config = \app\models\AlertConfig::findOne(['alertId' => $alert->id]);
        $drive   = new \app\models\api\DriveApi();
        //set date
        $config->start_date = DateHelper::asDatetime($config->start_date);
        $config->end_date = DateHelper::asDatetime($config->end_date);

        $sources = \app\models\AlertconfigSources::find()->where(['alertconfigId' => $config->id])->all();
        // set resources id select2
        foreach ($sources as $source) {
          $alert->alertResourceId[] = $source->alertResource->id;
        }
        // set dictionaryIds
        $keywords = \app\models\Keywords::find()->where(['alertId' => $alert->id])->select('dictionaryId')->all();
        
        foreach ($keywords as $keyword){
          if(!in_array($keyword->dictionary->name,$alert->dictionaryIds)){
            $alert->dictionaryIds[$keyword->dictionary->name] = $keyword->dictionary->name;
          }
        }
        //free words
        $keywords = \app\models\Keywords::find()->where(['alertId' => $alert->id,'dictionaryId' => 4])->select('name')->all();
        $free_words = [];
        if($keywords){
          foreach ($keywords as $keyword){
            $alert->free_words[] = $keyword->name;
          }
        }

        // set productIds
        $productsIds = \app\models\ProductsModelsAlerts::find()->where(['alertId' => $alert->id])->all();
        $product_models = [];
        foreach ($productsIds as $productsId) {
            $product_models[$productsId->productModel->id] = $productsId->productModel->name;
        }
        $alert->productsIds  = $product_models;
        // set tag 
        $config->product_description = explode(",",$config->product_description);
        $config->competitors = explode(",",$config->competitors);
        


        
        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post()) && $config->load(Yii::$app->request->post())) {
          $error = false;
          $alert->userId = 1;
          if(!$alert->save()){ 
            $error = true;
          }
          // config model
          $config->alertId = $alert->id;
          $config->product_description = (Yii::$app->request->post('AlertConfig')['product_description'])
                                          ? implode(",",Yii::$app->request->post('AlertConfig')['product_description']) : '';
          $config->competitors = (Yii::$app->request->post('AlertConfig')['competitors'])
                                          ? implode(",",Yii::$app->request->post('AlertConfig')['competitors']) : '';                                

          if($config->save()){
              //sources model
              $is_save_socialIds = $config->saveAlertconfigSources($alert->alertResourceId);
              if(!$is_save_socialIds){
                $error = true;
              }
          }else{ $error = true;}
          // keywords/ dictionaryIds model
          \app\models\Keywords::deleteAll('alertId = '.$alert->id);
          $dictionaryIds = Yii::$app->request->post('Alerts')['dictionaryIds'];
          if($dictionaryIds){
            $model = new \app\models\Dictionaries();
            $dictionaries = $model->getOrSaveDictionary($dictionaryIds);
            if($dictionaries){
              foreach ($dictionaries as $dictionaryId => $dictionaryName){
                $keywords_drive = $drive->getContentDictionaryByTitle([$dictionaryName]);
                foreach ($keywords_drive[$dictionaryName] as $word) {
                  $models[] = [$alert->id,$dictionaryId,$word];
              }
                  
            }
            Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
                        ->execute();
            }
          }
          // if free words is
          $free_words = Yii::$app->request->post('Alerts')['free_words'];
          $model = []; 
          if ($free_words){
            foreach ($free_words as $word){
              $models[] = [$alert->id,4,$word];
            }
            // save free words 
            Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
            ->execute();
          }
          

          // set product/models
          \app\models\ProductsModelsAlerts::deleteAll('alertId = '.$alert->id);
          $products_models = Yii::$app->request->post('Alerts')['productsIds'];
          $model = new \app\models\Products();
          $productsIds = $model::getModelsIdByName($products_models);
          
          foreach ($productsIds as $id => $name) {
              $model = new \app\models\ProductsModelsAlerts();
              $model->alertId = $alert->id;
              $model->product_modelId = $id;
              if(!$model->save()){
                $error = true;
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
          return $this->redirect(['view', 'id' => $alert->id]);
        }

        return $this->render('update', [
            'alert' => $alert,
            'config' => $config,
            'sources' => $sources,
            'drive' => $drive,
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
