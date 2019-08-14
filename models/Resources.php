<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "resources".
 *
 * @property int $id
 * @property string $name
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertconfigSources[] $alertconfigSources
 * @property CredencialsApi[] $credencialsApis
 */
class Resources extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'resources';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 40],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertconfigSources()
    {
        return $this->hasMany(AlertconfigSources::className(), ['alertResourceId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredencialsApis()
    {
        return $this->hasMany(CredencialsApi::className(), ['resourceId' => 'id']);
    }
}
