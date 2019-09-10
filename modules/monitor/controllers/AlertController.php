<?php

namespace app\modules\monitor\controllers;

use Yii;
use app\models\Alerts;
use app\models\search\AlertSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

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
        $alert = new \app\models\Alerts();
        $config = new \app\models\AlertConfig();
        $sources = new \app\models\AlertconfigSources();
        $keywords = new \app\models\AlertsKeywords();

        

        if (Yii::$app->request->post() && $alert->load(Yii::$app->request->post())) {
            $error = false;
            $alert->userId = 1;
            if(!$alert->save()){ 
              $error = true;
            }
            // config model
            $config = new \app\models\AlertConfig();
            $config->uudi = uniqid();
            $config->country = 'Chile';
            $config->alertId = $alert->id;
            $config->start_date = strtotime(Yii::$app->request->post('AlertConfig')['start_date']);
            $config->end_date = strtotime(Yii::$app->request->post('AlertConfig')['end_date']);
            $config->competitors = implode(",",Yii::$app->request->post('AlertConfig')['competitors']);
            $config->product_description = implode(",",Yii::$app->request->post('AlertConfig')['product_description']);

            if($config->save()){
                //sources model
                $socialIds = Yii::$app->request->post('AlertconfigSources')['alertResourceId'];
                foreach($socialIds as $socialId){
                    $model = new \app\models\AlertconfigSources();
                    $model->alertconfigId = $config->id;
                    $model->alertResourceId = $socialId;
                    $model->save();
                }

            }else{ $error = true; }
            // keywords/ dictionaryIds model
            

            if($error){
              return $this->render('error',[
                  'name' => 'alert',
                  'message' => 'Alers not created.'
              ]);
            }
           
            Yii::$app->getSession()->setFlash('success', 'Alers has been created.');
            return $this->redirect(['view', 'id' => $alert->id]);
        } 


        return $this->render('create', [
            'alert' => $alert,
            'config' => $config,
            'sources' => $sources,
            'keywords' => $keywords,
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
        $model = new \app\models\form\AlertForm();
        $model->alerts = $this->findModel($id);
        //get resourcesIds
        $resourcesIds = \app\models\AlertconfigSources::find()->where(['alertconfigId' => $model->alerts->config->id])->all();
        $resources = [];
        foreach ($resourcesIds as $resourceId){
          // set to form model
          $resources[] = $resourceId->alertResourceId;
          //array_push($resources,\yii\helpers\ArrayHelper::map($resourceId,'alertconfigId','alertResourceId')); 
        }

        
       
        $model->setAttributes(Yii::$app->request->post());


        if (Yii::$app->request->post() && $model->save()) {

            $resourcesIds = Yii::$app->request->post('AlertForm')['resourcesId'];
            /* clear the categories of the post before saving */
            \app\models\AlertconfigSources::deleteAll(['alertconfigId' => $model->alerts->config->id]);
            foreach($resourcesIds as $resourceId) {
                $modelSources =  new \app\models\AlertconfigSources();
                $modelSources->alertconfigId = $model->alerts->config->id;  
                $modelSources->alertResourceId = $resourceId; 
                $modelSources->save(); 
            }

            return $this->redirect(['view', 'id' => $model->alerts->id]);
        }elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', [
            'model' => $model,
            'resources' => $resources,
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
