<?php

namespace app\models;

use Yii;
use Yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 * @property int $status
 * @property int $condicion
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Users $user
 * @property Keywords[] $keywords
 */
class Alerts extends \yii\db\ActiveRecord
{

    const STATUS_ACTIVE    = 1;
    const STATUS_INACTIVE  = 0;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts';
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
                'value' => function() { return date('U');  },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId'], 'required'],
            [['status'], 'default','value' => 1],
            [['userId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'userId'    => Yii::t('app', 'User ID'),
            'name'      => Yii::t('app', 'Name'),
            'status'    => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * [getBringAllAlertsToRun get all the alerts with resources,products only use to console actions]
     * @return [array] [if not alerts with condition return a empty array]
     */
    public function getBringAllAlertsToRun(){

        // get time
        $expression = new \yii\db\Expression('NOW()');
        $now = (new \yii\db\Query)->select($expression)->scalar();
        $timestamp = time($now);
        // get all alert with relation config with the condicion start_date less or equals to $timestamp
        $alerts = $this->find()->where([
            'status' => self::STATUS_ACTIVE,
        ])->with(['config' => function($query) use($timestamp) {
            $query->andWhere([
                'and',
                    ['<=', 'start_date', $timestamp],
                   // ['>=', 'end_date', $timestamp],
                ]);
            $query->with(['configSources.alertResource']);
        }
        ])->asArray()->all();

        $alertsConfig = [];
        // there is alert in the model
        if(!empty($alerts)){
            // loop searching alert with mentions relation and config relation
            for($a = 0; $a < sizeOf($alerts); $a++){
                if((!empty($alerts[$a]['config']))){
                    // reduce configSources.alertResource
                    for($s = 0; $s < sizeOf($alerts[$a]['config']['configSources']); $s ++){
                        $alertResource = ArrayHelper::getValue($alerts[$a]['config']['configSources'][$s], 'alertResource.name');
                        $alerts[$a]['config']['configSources'][$s] = $alertResource;
                    } // end for $alerts[$a]['config']['configSources']
                    array_push($alertsConfig, $alerts[$a]);
                } // end if not empty
            } // end loop alerts config
            //get family/products/models
            for($c = 0; $c < sizeOf($alertsConfig); $c++){
                $products_models_alerts = ProductsModelsAlerts::findAll(['alertId' => $alertsConfig[$c]['id']]);
                if(!empty($products_models_alerts)){
                    $alertsConfig[$c]['products'] = [];
                    foreach($products_models_alerts as $product){
                        array_push($alertsConfig[$c]['products'], $product->productModel->product->name);
                        array_push($alertsConfig[$c]['products'], $product->productModel->product->category->name);
                       // array_push($alertsConfig[$c]['products'], $product->productModel->product->category->productsFamily->name);
                    }
                }
            }
        }
        
       return $alertsConfig;
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfig()
    {
        return $this->hasOne(AlertConfig::className(), ['alertId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['alertId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertsMentions()
    {
        return $this->hasMany(AlertsMencions::className(), ['alertId' => 'id']);
    }

}
