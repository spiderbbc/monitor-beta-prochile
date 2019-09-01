<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = 'Update Alerts: ' . $model->alerts->name;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->alerts->name, 'url' => ['view', 'id' => $model->alerts->name]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="alerts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
