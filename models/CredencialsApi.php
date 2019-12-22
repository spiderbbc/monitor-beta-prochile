<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "credencials_api".
 *
 * @property int $id
 * @property int $userId
 * @property int $resourceId
 * @property string $name_app
 * @property string $api_key
 * @property string $api_secret_key
 * @property string $access_secret_token
 * @property string $status
 * @property string $bearer_token
 * @property string $apiLogin
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Resources $resource
 * @property Users $user
 */
class CredencialsApi extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'credencials_api';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId', 'resourceId'], 'required'],
            [['userId', 'resourceId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy','status'], 'integer'],
            [['name_app'], 'string', 'max' => 45],
           // [['api_key', 'api_secret_key', 'access_secret_token', 'bearer_token', 'apiLogin'], 'string', 'max' => 60],
            [['resourceId'], 'exist', 'skipOnError' => true, 'targetClass' => Resources::className(), 'targetAttribute' => ['resourceId' => 'id']],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'                  => Yii::t('app', 'ID'),
            'userId'              => Yii::t('app', 'User ID'),
            'resourceId'          => Yii::t('app', 'Resource ID'),
            'name_app'            => Yii::t('app', 'Name App'),
            'api_key'             => Yii::t('app', 'Api Key'),
            'api_secret_key'      => Yii::t('app', 'Api Secret Key'),
            'access_secret_token' => Yii::t('app', 'Access Secret Token'),
            'bearer_token'        => Yii::t('app', 'Bearer Token'),
            'apiLogin'            => Yii::t('app', 'Api Login'),
            'createdAt'           => Yii::t('app', 'Created At'),
            'updatedAt'           => Yii::t('app', 'Updated At'),
            'createdBy'           => Yii::t('app', 'Created By'),
            'updatedBy'           => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(Resources::className(), ['id' => 'resourceId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }
}
