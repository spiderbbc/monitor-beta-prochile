<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Alerts */

$this->title = Yii::t('app', 'Create Alerts');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Alerts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
		'alert'   => $alert,
		'config'  => $config,
		'drive'   => $drive,
    ]) ?>

</div>
