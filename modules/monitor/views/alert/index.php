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

            'id',
            'userId',
            'name',
            'status',
            'config.start_date:datetime',
            'config.end_date:datetime',
            //'updatedAt',
            //'createdBy',
            //'updatedBy',

            //['class' => 'yii\grid\ActionColumn'],
           ['class' => 'ramosisw\CImaterial\grid\ActionColumn'],
        ],
    ]); ?>


</div>
