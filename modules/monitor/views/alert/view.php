<?php

use yii\helpers\Html;
//use yii\widgets\DetailView;
use macgyer\yii2materializecss\widgets\data\DetailView;

\app\assets\SweetAlertAsset::register($this);
\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\DataTableAsset::register($this);
\app\assets\JqcloudAsset::register($this);
\app\assets\highchartsAsset::register($this);
\app\assets\GoogleChartAsset::register($this);
\app\assets\AppAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$input = "<input type='text' v-model='test' value='".$model->id."'>";

\yii\web\YiiAsset::register($this);
?>
<div id="alerts-view" class="alerts-view" style="padding-top: 10px">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= Html::hiddenInput('alertId', $model->id,['id' => 'alertId']); ?>

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
                        $html .= "<span class='label label-info'>{$alert->alertResource->name}</span><status-alert id={$alert->alertResource->id} :resourceids={$alert->alertResource->id}></status-alert>";
                    }
                    return $html;
                },

            ],
            'config.start_date:datetime',
            'config.end_date:datetime',
        ],
    ]) ?>

    <div v-if="isData">
        <modal-alert></modal-alert>
        <div class="row">
            <total-mentions :count="count">
        </div>
        <div class="row">
            <!-- <total-resources> -->
            <div class="col-md-12">
                <total-resources-chart>
            </div>
        </div>

        <div class="row">
            <list-mentions></list-mentions>
        </div>

        <div class="row">
            <cloud-words></cloud-words>
        </div>
        
        <div class="row">
            <resource-date-mentions></resource-date-mentions>
        </div>

        <div class="row">
            <list-emojis></list-emojis>
        </div>
        
    </div>
    <div v-else>
        <div class="loader">
          <div class="spinner" style="height: 15vh;width:  15vh;"></div>
        </div>
    </div>
 
     

</div>

<?= $this->render('_templates-vue');  ?>




