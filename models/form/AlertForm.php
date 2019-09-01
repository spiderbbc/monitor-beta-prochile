<?php

namespace app\models\form;

use Yii;
use yii\base\Model;
use app\models\Alerts;
use app\models\AlertConfig;

class AlertForm extends Model
{
    private $_alert;
    private $_alertConfig;
    public $start_date;
    public $end_date;

    /*public $id;
    public $name;
    public $start_date;
    public $end_date;
    public $document_file;
    public $resource;
    public $dictionary;
    public $keyword;
    public $product;
    public $web_url;
    public $product_description;
    public $competitors;
    public $countries;*/


    /*public function rules()
    {
        return [
            [['name','start_date', 'end_date'], 'required'],
            //['document_file', 'file'],
            [['resource', 'dictionary','product'], 'integer'],
            [['keyword','web_url'], 'string'],
            ['product_description', 'default','value' => 'tecnology'],
            ['resource', 'default','value' => 1],
            ['competitors', 'default','value' => 'Sony,Huawei'],
            ['countries', 'default','value' => 'Chile'],
        ];    
    }*/

    public function rules()
    {
        return [
           [['Alerts'], 'required'],
           [['AlertConfig'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'start_date'        => Yii::t('app', 'Fecha de Inicio'),
            'end_date' => Yii::t('app','Fecha Final'),
            'document_file' => Yii::t('app','Documentos'),
            'resource'    => Yii::t('app', 'Plataformas Sociales'),
            'dictionary' => Yii::t('app','Diccionarios de Palabras'),
            'keyword' => Yii::t('app', 'Palabras Libres'),
            'product' => Yii::t('app', 'Categorias - Productos - Modelos'),
            'web_url' => Yii::t('app', 'direcciones web (Urls)'),
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
            $this->alertConfig->countries = 'chile';
            $this->alertConfig->competitors = 'chile';
            $this->alertConfig->product_description = 'chile';
            $this->alertConfig->start_date = \app\helpers\DateHelper::asTimestamp($alertConfig['start_date']);
            $this->alertConfig->end_date = \app\helpers\DateHelper::asTimestamp($alertConfig['end_date']);
            $this->alertConfig->setAttributes($alertConfig);
        } elseif ($alertsConfig instanceof AlertConfig) {
            $this->_alertConfig = $alertsConfig;
        }
    }

    public function errorSummary($form)
    {
        $errorLists = [];
        foreach ($this->getAllModels() as $id => $model) {
            $errorList = $form->errorSummary($model, [
                'header' => '<p>Please fix the following errors for <b>' . $id . '</b></p>',
            ]);
            $errorList = str_replace('<li></li>', '', $errorList); // remove the empty error
            $errorLists[] = $errorList;
        }
        return implode('', $errorLists);
    }


    private function getAllModels()
    {
        return [
            'Alerts' => $this->alerts,
            'AlertConfig' => $this->alertConfig,
        ];
    }


}
