<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = Yii::t('app', 'Update Alerts: {name}', [
    'name' => $alert->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Alerts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $alert->name, 'url' => ['view', 'id' => $alert->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="alerts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
		'alert'   => $alert,
		'config'  => $config,
		'sources' => $sources,
		'drive'   => $drive,
    ]) ?>

</div>
