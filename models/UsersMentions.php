<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "users_mentions".
 *
 * @property int $id
 * @property int $user_uuid
 * @property string $name
 * @property array $user_data
 * @property string $subject
 * @property string $message
 * @property string $screen_name
 * @property string $profile_image_url
 * @property int $createdAt
 * @property int $updatedAt
 *
 * @property Mentions[] $mentions
 */
class UsersMentions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'users_mentions';
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
            [['user_uuid', 'createdAt', 'updatedAt'], 'integer'],
            [['name', 'screen_name'], 'required'],
            [['user_data'], 'safe'],
            [['name', 'subject', 'message', 'screen_name', 'profile_image_url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_uuid' => 'User Uuid',
            'name' => 'Name',
            'user_data' => 'User Data',
            'subject' => 'Subject',
            'message' => 'Message',
            'screen_name' => 'Screen Name',
            'profile_image_url' => 'Profile Image Url',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMentions()
    {
        return $this->hasMany(Mentions::className(), ['origin_id' => 'id']);
    }
}
