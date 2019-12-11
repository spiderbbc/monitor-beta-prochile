<?php

use yii\helpers\Html;
//use yii\widgets\DetailView;
use macgyer\yii2materializecss\widgets\data\DetailView;

\app\assets\AxiosAsset::register($this);
\app\assets\VueAsset::register($this);
\app\assets\DataTableAsset::register($this);
\app\assets\JqcloudAsset::register($this);
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
                        $html .= " <span class='label label-info'>{$alert->alertResource->name}</span>";
                    }
                    return $html;
                },

            ],
            'config.start_date:datetime',
            'config.end_date:datetime',
        ],
    ]) ?>
    

    <div v-if="isData">
        <div class="row">
            <total-mentions :count="count">
        </div>
        <div class="row">
            <total-resources v-for="(value, resource) in resourcescount"  :resourcescount="resourcescount" :value="value" :resource="resource" :key="resource">
        </div>

        <list-mentions></list-mentions>

        <cloud-words></cloud-words>
        
    </div>
    <div v-else>
        loading Animation ....
    </div>
 
     

</div>



<!-- template que muestra el total de todas las menciones -->
<script type="text/x-template" id="view-total-mentions">
    <div class="col-md-12 well">
        <h2>Total de Menciones: {{count}}</h2>
    </div>
</script>

<!-- template que muestra el total de todas las menciones por Red Social -->
<script type="text/x-template" id="view-total-mentions-resources">
    <div :class="columns" style="margin-right: 10px;">
        <h4><a :href="fetchResourceName">{{resource}}:</a></h4>
        <p>{{value}}</p>
    </div>
</script>


<!-- template que muestra todas las menciones -->
<script type="text/x-template" id="list-mentions">
    <div>
        <h4>Menciones</h4>
        <div class="row">
            <div class="col-md-12">
                <table id="list-mentions" class="table table-striped table-bordered" cellspacing="0"  style="width:100%">
                    <thead>
                        <tr>
                            <th>Recurso</th>
                            <th>Producto</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Titulo</th>
                            <th>mensaje</th>
                            <th>Url</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th>Recurso</th>
                            <th>Producto</th>
                            <th>Fecha</th>
                            <th>Nombre</th>
                            <th>Usuario</th>
                            <th>Titulo</th>
                            <th>mensaje</th>
                            <th>Url</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</script>


<script type="text/x-template" id="cloud-words">
    <div v-if="loaded" class="col-md-12 well">
        <h1>Cloud words</h1>
        <div id="jqcloud" class="jqcloud"></div>
    </div>    
</script>



