<?php

namespace app\modules\user\models;

use Yii;

/**
 * This is the model class for table "user_logs".
 *
 * @property int $id
 * @property int $userId
 * @property string|null $remote_addr
 * @property string|null $log_date
 * @property string|null $message
 * @property string|null $user_agent
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property Users $user
 */
class UserLogs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId'], 'required'],
            [['userId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['log_date', 'user_agent'], 'safe'],
            [['message'], 'string'],
            [['remote_addr'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'userId' => 'User ID',
            'remote_addr' => 'Remote Addr',
            'log_date' => 'Log Date',
            'message' => 'Message',
            'user_agent' => 'User Agent',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }
}
