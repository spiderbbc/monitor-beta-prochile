<?php

use yii\helpers\Html;
//use yii\widgets\DetailView;
use macgyer\yii2materializecss\widgets\data\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="alerts-view" style="padding-top: 10px">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => Yii::t('app','Usuario'),
                'attribute' => 'userId',
                'format' => 'raw',
                'value' => function($model){
                    return $model->user->username;
                }
            ],
            [
                'label' => Yii::t('app','Nombre de la Alerta'),
                'attribute' => 'name',
                'format' => 'raw',
                'value' => function($model) {
                  return $model->name;
                }
            ],
            [
                'label' => Yii::t('app','Estado'),
                'format'    => 'raw',
                'attribute' => 'status',
                'value' => function($model) {
                    return ($model->status) ? 'Active' : 'Inactive';
                }
            ],
            [
                'label' => Yii::t('app','Recursos Sociales'),
                'format'    => 'raw',
                'attribute' => 'alertResourceId',
                'value' => function($model) {
                    $html = '';
                    foreach ($model->config->configSources as $alert) {
                        $html .= " <span class='label label-info'>{$alert->alertResource->name}</span>";
                    }
                    return $html;
                },

            ],
            'config.start_date:datetime',
            'config.end_date:datetime',
        ],
    ]) ?>

</div>
