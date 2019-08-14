<?php

namespace app\models;

use Yii;
use Yii\helpers\ArrayHelper;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property int $userId
 * @property string $uudi
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
    
    const CONDITION_WAIT   = 2;
    const CONDITION_ACTIVE = 1;
    const CONDITION_FINISH = 0;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'uudi'], 'required'],
            [['userId', 'status', 'condicion', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['uudi', 'name'], 'string', 'max' => 255],
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
            'uudi'      => Yii::t('app', 'Uudi'),
            'name'      => Yii::t('app', 'Name'),
            'status'    => Yii::t('app', 'Status'),
            'condicion' => Yii::t('app', 'Condicion'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }


    public function getBringAllAlertsToRun(){

        $expression = new \yii\db\Expression('NOW()');
        $now = (new \yii\db\Query)->select($expression)->scalar();
        $timestamp = time($now);
        $alertConfig = AlertConfig::find()->where(['>=','end_date',$timestamp])->with(['alert' => function($query){
            $query->andWhere([
                'and',
                ['=','status',self::STATUS_ACTIVE],
                ['=','condicion',self::CONDITION_ACTIVE],
            ]);
        }])->asArray()->all();

        $alert = [];
        for($a = 0; $a < sizeOf($alertConfig); $a++){
            if(!is_null($alertConfig[$a]['alert'])){
                $id = $alertConfig[$a]['id'];
                $alert_config_sources = AlertconfigSources::find()->where(['alertconfigId' => $id])->with(['alertResource'])->asArray()->all();
                $resources = ArrayHelper::getColumn($alert_config_sources,'alertResource.name');
                $alertConfig[$a]['resources'] = $resources;
            }
        }
        
       return $alertConfig;
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
}
