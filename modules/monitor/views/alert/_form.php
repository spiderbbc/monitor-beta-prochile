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
use mludvik\tagsinput\TagsInputWidget;

\app\assets\AxiosAsset::register($this);

/* @var $this yii\web\View */
/* @var $model app\models\form\AlertForm */
/* @var $form ActiveForm */
$pluginOptions = [ 'allowClear' =>  true];
if (!$alert->isNewRecord) {
    $pluginOptions = [ 'allowClear' =>  false];
}
?>
<div id="views-alert" class="modules-monitor-views-alert">
    <?php $form = ActiveForm::begin(); ?>
    <?= 
        $form->field($config, 'product_description')->hiddenInput(['id' => 'product_description'])->label(false);
    ?>
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
                            'type' => DatePicker::TYPE_INPUT,
                            'options' => ['id' => 'start_date','name' => 'start_date','placeholder' => 'Ingrese Fecha Inicio'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'todayHighlight' => true,
                                'autoclose' => true,
                             //   'endDate' => '+28D',
                            ],
                            'pluginEvents' => [
                               "changeDate" => "function(e) {  validator_date(e); }",
                            ],
                        ]); 
                    ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($config, 'end_date')->widget(DatePicker::classname(), [
                        'type' => DatePicker::TYPE_INPUT,
                            'options' => ['id' => 'end_date','name' => 'end_date','placeholder' => 'Ingrese Fecha Final'],
                            'pluginOptions' => [
                                'orientation' => 'down left',
                                'format' => 'dd/mm/yyyy',
                                'todayHighlight' => true,
                                'autoclose' => true,
                            ],
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
                                'placeholder' => 'Selecione la red Social',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'debug' => false,
                                'value' => (isset($alert->config->configSourcesByAlertResource)) 
                                            ? $alert->config->configSourcesByAlertResource : [],
                               
                            ],
                            'pluginOptions' => $pluginOptions,
                            'pluginEvents' => [
                               "select2:select" => "function(e) {
                                    var resourceName = e.params.data.text; 
                                    return modalReosurces(resourceName);
                               }",
                            ],
                            'toggleAllSettings' => [
                               'selectLabel' => '',
                               'unselectLabel' => '',
                               'selectOptions' => ['class' => 'text-success'],
                               'unselectOptions' => ['class' => 'text-danger'],
                            ],
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'urls')->widget(Select2::classname(), [
                    'options' => [
                        'id' => 'urls',
                        //'resourceName' => 'Product Competition',
                        'placeholder' => 'Ingrese url a Buscar', 
                        'multiple' => true
                    ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'minimumInputLength' => 2
                        ],
                    ]); 
                    ?> 
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'productsIds')->widget(Select2::classname(), [
                    'options' => [
                            'id' => 'productsIds',
                            'placeholder' => 'Ingrese terminos a buscar', 
                            'multiple' => true,
                            'value'=> ($alert->productsIds) ? $alert->productsIds : ['test'] 
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [','],
                            'minimumInputLength' => 2,
                            'maximumSelectionLength' => 20
                        ],
                    ])->label('Terminos a buscar');
                    ?>
                </div>
            </div>
            <!-- config properties-->
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($alert, 'dictionaryIds')->widget(Select2::classname(), [
                            'data' => $drive->dictionaries,
                            'options' => [
                                'id' => 'social_dictionaryId',
                                'resourceName' => 'dictionaries',
                                'placeholder' => 'Selecione Diccionarios de Palabras',
                                'multiple' => true,
                                'theme' => 'krajee',
                                'value' => (isset($alert->dictionariesName)) ? $alert->dictionariesName : [],
                            ],
                            'pluginOptions' => [
                                'allowClear' => false,
                            ],
                            'pluginEvents' => [
                               "select2:select" => "function(e) { 
                                    return null;
                               }",
                            ],
                            'toggleAllSettings' => [
                               'selectLabel' => '',
                               'unselectLabel' => '',
                               'selectOptions' => ['class' => 'text-success'],
                               'unselectOptions' => ['class' => 'text-danger'],
                            ],
                        ]);
                    ?>
                </div>
                <div class="col-md-4">
                    <?= $form->field($alert, 'free_words')->widget(Select2::classname(), [
                    'changeOnReset' => false,
                    'options' => [
                            'id' => 'free_words',
                            'resourceName' => 'Free Words',
                            'placeholder' => 'Ingrese Palabras Libres', 
                            'multiple' => true,
                        ],
                        'pluginOptions' => [
                            'tags' => true,
                            'tokenSeparators' => [',', ' '],
                            'minimumInputLength' => 2
                        ],
                    ]); 
                    ?>  
                </div>
                <div class="col-md-4">
                    <?= $form->field($config, 'lang')->widget(Select2::classname(), [
                    'data' => $config->langs,
                    'options' => [
                        'id' => 'language',
                        'placeholder' => 'Selecciona el Idioma', 
                        'value' => $config->lang
                    ],
                        'pluginOptions' => [
                        ],
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



<?php 
$userId = Yii::$app->user->id;
Yii::$app->view->registerJs('var userId = "'. $userId.'";var appId = "'. Yii::$app->id.'" ',  \yii\web\View::POS_HEAD);
$this->registerJsFile(
    '@web/js/app/form.js',
    ['depends' => [
        \app\assets\VueAsset::className(),
        \app\assets\SweetAlertAsset::className(),
        \app\assets\MomentAsset::className()
        ]
    ]
);

if (!$alert->isNewRecord) {
    Yii::$app->view->registerJs('var alertId = "'. $alert->id.'";var appId = "'. Yii::$app->id.'" ',  \yii\web\View::POS_HEAD);
    $this->registerJsFile(
    '@web/js/app/update.js',
    ['depends' => [
        \app\assets\SweetAlertAsset::className(),
        ]
    ]
);
}

?>