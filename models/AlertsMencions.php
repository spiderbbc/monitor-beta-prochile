<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "alerts_mencions".
 *
 * @property int $id
 * @property int $alertId
 * @property int $resourcesId
 * @property string $condition
 * @property string $type
 * @property array $product_obj
 * @property array $publication_id
 * @property array $next
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Alerts $alert
 * @property Resources $resources
 * @property MentionsPostfrom[] $mentionsPostfroms
 */
class AlertsMencions extends \yii\db\ActiveRecord
{

    const CONDITION_WAIT   = "WAIT";
    const CONDITION_ACTIVE = "ACTIVE";
    const CONDITION_FINISH = "FINISH";
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts_mencions';
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
            [['alertId', 'resourcesId'], 'required'],
            [['alertId', 'resourcesId','since_id','max_id', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
          //  [['product_obj'], 'safe'],
            [['condition', 'type','next','publication_id'], 'string', 'max' => 255],
            [['alertId'], 'exist', 'skipOnError' => true, 'targetClass' => Alerts::className(), 'targetAttribute' => ['alertId' => 'id']],
            [['resourcesId'], 'exist', 'skipOnError' => true, 'targetClass' => Resources::className(), 'targetAttribute' => ['resourcesId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertId' => Yii::t('app', 'Alert ID'),
            'resourcesId' => Yii::t('app', 'Resources ID'),
            'condition' => Yii::t('app', 'Condition'),
            'type' => Yii::t('app', 'Type'),
            'product_obj' => Yii::t('app', 'Product Obj'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
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
    public function getResources()
    {
        return $this->hasOne(Resources::className(), ['id' => 'resourcesId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMentionsPostfroms()
    {
        return $this->hasMany(MentionsPostfrom::className(), ['alert_mentionId' => 'id']);
    }
}
