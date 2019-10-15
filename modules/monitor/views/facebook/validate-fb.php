<?php 
use yii\helpers\Html;
$linkLogout = (isset($linkLogout)) ? $linkLogout : '';
?>
<div class="panel panel-body">
    <div class="page-header">
        <h1>Facebook API Test Results </h1>
    </div>
    <?= $out ?>    
    <hr>
    <?= Html::a('« Return', $linkLogout, ['class'=>'btn btn-success']) ?>
</div>