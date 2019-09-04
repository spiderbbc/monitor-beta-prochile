<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\form\AlertForm */
/* @var $form ActiveForm */
?>
<div class="modules-monitor-views-alert">
    <?php $form = ActiveForm::begin(); ?>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model->alerts, 'name') ?>        
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model->alertConfig, 'start_date')->widget(\yii\jui\DatePicker::className(), [
                        'inline' => false,
                        'language' => 'es',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options'=>['class'=>'form-control']
                    ]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model->alertConfig, 'end_date')->widget(\yii\jui\DatePicker::className(), [
                        'inline' => false,
                        'language' => 'es',
                        'dateFormat' => 'yyyy-MM-dd',
                        'options'=>['class'=>'form-control']
                    ]) ?>
                </div>
            </div>
            
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- modules-monitor-views-alert -->
