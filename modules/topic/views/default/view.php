<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\topic\models\MTopics */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'M Topics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="mtopics-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            //'id',
            //'userId',
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            'name',
            [
                'label' => Yii::t('app','Fecha Final'),
                'format'    => 'raw',
                'attribute' => 'end_date',
                'value' => function($model) {
                    date_default_timezone_set('UTC');
                    return date('Y-m-d',$model->end_date);
                }
            ],
            //'end_date:datetime',
            /*'createdAt',
            'updatedAt',
            'createdBy',
            'updatedBy',*/
        ],
    ]) ?>

</div>
