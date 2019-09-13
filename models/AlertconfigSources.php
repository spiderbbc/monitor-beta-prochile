<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alertconfig_sources".
 *
 * @property int $id
 * @property int $alertconfigId
 * @property int $alertResourceId
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertConfig $alertconfig
 * @property Resources $alertResource
 */
class AlertconfigSources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alertconfig_sources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alertconfigId', 'alertResourceId'], 'required'],
            [['alertconfigId', 'alertResourceId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['alertconfigId'], 'exist', 'skipOnError' => true, 'targetClass' => AlertConfig::className(), 'targetAttribute' => ['alertconfigId' => 'id']],
            [['alertResourceId'], 'exist', 'skipOnError' => true, 'targetClass' => Resources::className(), 'targetAttribute' => ['alertResourceId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'alertconfigId' => Yii::t('app', 'Alertconfig ID'),
            'alertResourceId' => Yii::t('app', 'Alert Resource ID'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertconfig()
    {
        return $this->hasOne(AlertConfig::className(), ['id' => 'alertconfigId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertResource()
    {
        return $this->hasOne(Resources::className(), ['id' => 'alertResourceId']);
    }
}
