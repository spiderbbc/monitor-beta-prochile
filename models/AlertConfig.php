<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "alert_config".
 *
 * @property int $id
 * @property int $alertId
 * @property string $product_description
 * @property string $competitors
 * @property string $country
 * @property string $url_drive
 * @property int $start_date
 * @property int $end_date
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertconfigSources[] $alertconfigSources
 */
class AlertConfig extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alert_config';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt','updatedAt'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updatedAt'],
                ],
              //  'value' => function() { return date('U');  },
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $uudi = uniqid();
        return [
            [['start_date','end_date'], 'required'],
            [['start_date','end_date'], 'date','format' => 'php:U'],
            [['alertId'], 'exist', 
              'skipOnError' => true, 
              'targetClass' => Alerts::className(), 
              'targetAttribute' => ['alertId' => 'id']]
        ];
    }

    public function beforeValidate()
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        
        // ...custom code here...
        $this->uudi = uniqid();
        $this->country = 'Chile';
        $this->start_date = strtotime(str_replace('/','-',$this->start_date));
        $this->end_date   = strtotime(str_replace('/','-',$this->end_date));
        /*$this->competitors         = ($this->competitors) ? implode(",",$this->competitors) : '';
        $this->product_description = ($this->product_description) ? implode(",",$this->product_description) : '';*/
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertId' => Yii::t('app', 'Alert ID'),
            'product_description' => Yii::t('app', 'Product Description'),
            'competitors' => Yii::t('app', 'Competitors'),
            'country' => Yii::t('app', 'url Drive'),
            'country' => Yii::t('app', 'country'),
            'start_date' => Yii::t('app', 'Start Date'),
            'end_date' => Yii::t('app', 'End Date'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    public function saveAlertconfigSources($socialIds){
      if($socialIds){
        \app\models\AlertconfigSources::deleteAll('alertconfigId = '.$this->id);
        foreach($socialIds as $socialId){
            $model = new \app\models\AlertconfigSources();
            $model->alertconfigId = $this->id;
            $model->alertResourceId = $socialId;
            if(!$model->save()){
              return true;
            }
        }
      }
      return true;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlert()
    {
        return $this->hasOne(Alerts::className(), ['id' => 'alertId']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfigSources()
    {
        return $this->hasMany(AlertconfigSources::className(), ['alertconfigId' => 'id']);
    }
}
