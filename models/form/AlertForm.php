<?php

namespace app\models\form;

use Yii;
use yii\base\Model;
use app\models\Alerts;
use app\models\AlertConfig;
use app\models\AlertconfigSources;

use app\helpers\DateHelper;

class AlertForm extends Model
{
    private $_alert;
    private $_alertConfig;
    private $_alertconfigSources;
    
    public $start_date;
    public $end_date;


    public function rules()
    {
        return [
           [['Alerts'], 'required'],
           [['AlertConfig','AlertconfigSources'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'start_date'    => Yii::t('app', 'Fecha de Inicio'),
            'end_date'      => Yii::t('app','Fecha Final'),
            'document_file' => Yii::t('app','Documentos'),
            'resource'      => Yii::t('app', 'Plataformas Sociales'),
            'dictionary'    => Yii::t('app','Diccionarios de Palabras'),
            'keyword'       => Yii::t('app', 'Palabras Libres'),
            'product'       => Yii::t('app', 'Categorias - Productos - Modelos'),
            'web_url'       => Yii::t('app', 'direcciones web (Urls)'),
        ];
    }

    public function afterValidate()
    {
        $error = false;
        
       
        if (!$this->alerts->validate()) {
            $error = true;
        }
        if (!$this->alertConfig->validate()) {
            
            $error = true;
        }
        if ($error) {
            $this->addError(null); // add an empty error to prevent saving
        }
        parent::afterValidate();
    }

    public function save()
    {
        if (!$this->validate()) {
            return false;
        }
        $transaction = Yii::$app->db->beginTransaction();
        if (!$this->alerts->save()) {
            $transaction->rollBack();
            return false;
        }
        $this->alertConfig->alertId = $this->alerts->id;
        
        if (!$this->alertConfig->save(false)) {
            $transaction->rollBack();
            return false;
        }
        $transaction->commit();
        return true;
    }

    public function getAlerts()
    {
        return $this->_alert;
    }

    public function setAlerts($alerts)
    {
        if ($alerts instanceof Alerts) {
            $this->_alert = $alerts;
        } else if (is_array($alerts)) {
            $this->_alert->setAttributes($alerts);
        }
    }

    public function getAlertConfig()
    {
        if ($this->_alertConfig === null) {
            if ($this->alerts->isNewRecord) {
                $this->_alertConfig = new AlertConfig();
                $this->_alertConfig->loadDefaultValues();
            } else {
                $this->_alertConfig = $this->alerts->config;
            }
        }
        return $this->_alertConfig;
    }

    public function setAlertConfig($alertConfig)
    {
        if (is_array($alertConfig)) {
            $this->alertConfig->uudi = uniqid();
            $this->alertConfig->country = null;
            $this->alertConfig->competitors = 'chile';
            $this->alertConfig->product_description = 'chile';

            $this->alertConfig->start_date = DateHelper::asTimestamp($alertConfig['start_date']);
            $this->alertConfig->end_date =   DateHelper::asTimestamp($alertConfig['end_date']);
           
            $this->alertConfig->setAttributes($alertConfig);
        } elseif ($alertsConfig instanceof AlertConfig) {
            $this->_alertConfig = $alertsConfig;
        }
    }

    public function getAlertConfigSources(){
        return $this->_alertconfigSources;
    }

    public function setAlertConfigSources($alertConfigSources){
        if ($alertConfigSources instanceof AlertconfigSources) {
            $this->_alertconfigSources = $alertConfigSources;
        } else if (is_array($alertConfigSources)) {
            $this->_alertconfigSources->setAttributes($alertConfigSources);
        }
    }

    public function errorSummary($form)
    {
        $errorLists = [];
        foreach ($this->getAllModels() as $id => $model) {
            $errorList = $form->errorSummary($model, [
                'header' => '<div class="alert alert-warning" role="alert">
                              Please fix the following errors for ' . $id .'
                            </div>',
            ]);
            $errorList = str_replace('<div></div>', '', $errorList); // remove the empty error
            $errorLists[] = $errorList;
        }
        return implode('', $errorLists);
    }


    private function getAllModels()
    {
        return [
            'Alerts' => $this->alerts,
            'AlertConfig' => $this->alertConfig,
            'AlertconfigSources' => $this->alertconfigSources,
        ];
    }


}
