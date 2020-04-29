<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
/* @var $this yii\web\View */
/* @var $searchModel app\modules\topic\models\MTopicsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'M Topics');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mtopics-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create M Topics'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                //'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                  return Html::a($model->name,['update', 'id' => $model->id]);
                }
            ],
            [
                'label' => Yii::t('app', 'Fecha Final'),
                'attribute' => 'end_date',
                'format' => 'raw',
                'value' => function($model) { 
                    date_default_timezone_set('UTC');
                    return Html::a(date('Y-m-d',$model->end_date), ['update', 'id' => $model->id]);
                },
                'filter' => \kartik\date\DatePicker::widget([
                    'name' => 'MTopicsSearch[end_date]',
                    'type' => \kartik\date\DatePicker::TYPE_COMPONENT_APPEND,
                    'value' => $searchModel['end_date'],
                    'pluginOptions' => [
                        'autoclose' => true,
                        'format' => 'yyyy/mm/dd',
                    ]
                ]),
            ],
            'status',
            //'createdAt',
            //'updatedAt',
            //'createdBy',
            //'updatedBy',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
