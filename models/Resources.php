<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "resources".
 *
 * @property int $id
 * @property int $resourcesId
 * @property string $name
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property AlertconfigSources[] $alertconfigSources
 * @property AlertsMencions[] $alertsMencions
 * @property CredencialsApi[] $credencialsApis
 * @property TypeResources $resources
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
            [['resourcesId', 'name'], 'required'],
            [['resourcesId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 40],
            [['resourcesId'], 'exist', 'skipOnError' => true, 'targetClass' => TypeResources::className(), 'targetAttribute' => ['resourcesId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => Yii::t('app', 'ID'),
            'resourcesId' => Yii::t('app', 'Resources ID'),
            'name'        => Yii::t('app', 'Name'),
            'createdAt'   => Yii::t('app', 'Created At'),
            'updatedAt'   => Yii::t('app', 'Updated At'),
            'createdBy'   => Yii::t('app', 'Created By'),
            'updatedBy'   => Yii::t('app', 'Updated By'),
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
    public function getAlertsMencions()
    {
        return $this->hasMany(AlertsMencions::className(), ['resourcesId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCredencialsApis()
    {
        return $this->hasMany(CredencialsApi::className(), ['resourceId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResources()
    {
        return $this->hasOne(TypeResources::className(), ['id' => 'resourcesId']);
    }
}
