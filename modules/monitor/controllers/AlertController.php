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
        $model = new \app\models\form\AlertForm();
        $model->alerts = new \app\models\Alerts();
        $model->alertconfigSources = new \app\models\AlertconfigSources();
        $model->alerts->userId = 1;
        
        $model->setAttributes(Yii::$app->request->post());
        if (Yii::$app->request->post() && $model->save()) {
            Yii::$app->getSession()->setFlash('success', 'Alers has been created.');
            return $this->redirect(['view', 'id' => $model->alerts->id]);
        } elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }
        return $this->render('create', ['model' => $model]);
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
        $model->setAttributes(Yii::$app->request->post());


        if (Yii::$app->request->post() && $model->save()) {
            return $this->redirect(['view', 'id' => $model->alerts->id]);
        }elseif (!Yii::$app->request->isPost) {
            $model->load(Yii::$app->request->get());
        }

        return $this->render('update', [
            'model' => $model,
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
