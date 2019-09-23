<?php

use yii\helpers\Html;
//use yii\grid\GridView;
use macgyer\yii2materializecss\widgets\grid\GridView;
/* @var $this yii\web\View */
/* @var $searchModel app\models\search\AlertSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alerts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alerts-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Alerts', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'label' => Yii::t('app','Usuario'),
                'attribute' => 'userId',
                'value' => function($model){
                    return $model->user->username;
                }
            ],
            [
                'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
            ],
            [
                'label' => Yii::t('app','Estado'),
                'attribute' => 'status'
            ],
            [
                'label' => Yii::t('app', 'Fecha de Inicio'),
                'attribute' => 'start_date',
                'value' => function($model) { 
                    return Yii::$app->formatter->asDatetime($model->config->start_date,'yyyy-MM-dd');  
                },
            ],
            [
                'label' => Yii::t('app', 'Fecha Final'),
                'attribute' => 'end_date',
                'value' => function($model) { 
                    return Yii::$app->formatter->asDatetime($model->config->end_date,'yyyy-MM-dd');  
                },
            ],
            //'updatedAt',
            //'createdBy',
            //'updatedBy',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
