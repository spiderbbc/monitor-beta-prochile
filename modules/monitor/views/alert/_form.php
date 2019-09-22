<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\helpers\ArrayHelper;

use app\models\Products;
use app\models\Resources;

use kartik\select2\Select2;
use kartik\date\DatePicker;
use kartik\file\FileInput;
use mludvik\tagsinput\TagsInputWidget

/* @var $this yii\web\View */
/* @var $model app\models\form\AlertForm */
/* @var $form ActiveForm */

?>
<div class="modules-monitor-views-alert">
    <?php $form = ActiveForm::begin(); ?>
        <div class="row">
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($alert, 'name') ?>  
                </div>
            </div>
            <!-- dates -->
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($config, 'start_date')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'Enter start date ...'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'autoclose' => true,
                            ]
                        ]); 
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($config, 'end_date')->widget(DatePicker::classname(), [
                            'options' => ['placeholder' => 'Enter end date ...'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'autoclose' => true,
                            ]
                        ]); 
                    ?>
                </div>
            </div>
            <!-- dictionaries and social -->
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($alert, 'alertResourceId')->widget(Select2::classname(), [
                            'data' => $alert->social,
                            'options' => [
                                'id' => 'social_resourcesId',
                                'placeholder' => 'Select a resources...',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'debug' => true,
                                'value' => (isset($alert->config->configSourcesByAlertResource)) 
                                            ? $alert->config->configSourcesByAlertResource : [],
                               
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ]
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'dictionaryIds')->widget(Select2::classname(), [
                            'data' => $drive->dictionaries,
                            'options' => [
                                'id' => 'social_dictionaryId',
                                'placeholder' => 'Select a dictionaries...',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'debug' => true,
                                'value' => (isset($alert->keywordsIds)) ? $alert->keywordsIds : [],
                            ],
                            'pluginOptions' => [
                                'depends'=>['drive-title'],
                                'allowClear' => true,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ]
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'productsIds')->widget(Select2::classname(), [
                            'data' => Products::getProducts(),
                            'options' => [
                                'id' => 'productsIds',
                                'placeholder' => 'Select a products...',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'debug' => true,
                                //'value' => [1 => 'LG G7 ThinQ (G710 / New Aurora Black'],
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'tags' => true,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ]
                        ]);
                    ?>
                </div>
            </div>
            <!-- config properties-->
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($alert, 'free_words')->widget(Select2::classname(), [
                   // 'data' => $alert->freeKeywords,
                    'options' => [
                            'placeholder' => 'write a tags free words ...', 
                            'multiple' => true,
                          //  'value' => (isset($alert->freeKeywords)) ? $alert->freeKeywords : [],
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                           // 'maximumInputLength' => 10
                        ],
                    ])->label('Tag free words'); 
                    ?>   
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'product_description')->widget(Select2::classname(), [
                    //'data' => $data,
                    'options' => ['placeholder' => 'write a tags product description ...', 
                                   'multiple' => true,
                                  // 'value' => [$config->product_description]
                               ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            //'maximumInputLength' => 10
                        ],
                    ])->label('Tag product description'); 
                    ?>   
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'competitors')->widget(Select2::classname(), [
                    //'data' => $data,
                    'options' => ['placeholder' => 'write a tags competitors ...', 'multiple' => true],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                          //  'maximumInputLength' => 10
                        ],
                    ])->label('Tag competitors'); 
                    ?> 
                </div>
            </div>
            <!-- files -->
            <div class="row">
                <div class="col-md-12">
                    <?= $form->field($alert, 'files')->widget(FileInput::classname(), [
                        'options' => ['accept' => 'text/csv'],
                    ]); 
                    ?>
                </div>
            </div>
                     
            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    <?php ActiveForm::end(); ?>

</div><!-- modules-monitor-views-alert -->
