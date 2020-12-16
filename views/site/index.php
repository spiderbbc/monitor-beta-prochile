<?php

/* @var $this yii\web\View */

$this->title = 'ProChile';
?>
<div class="site-index">

    <div class="body-content">
        <?= app\widgets\AlertFacebook::widget() ?>
        <?= app\widgets\insights\InsightsWidget::widget() ?>
    </div>
</div>
