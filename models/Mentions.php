<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mentions".
 *
 * @property int $id
 * @property int $alert_mentionId
 * @property int $origin_id
 * @property int $created_time
 * @property string $mention_data
 * @property string $subject
 * @property string $message
 * @property string $message_markup
 * @property string $url
 * @property string $domain_url
 * @property string $location
 * @property int $social_id
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property KeywordsMentions[] $keywordsMentions
 * @property AlertsMencions $alertMention
 * @property UsersMentions $origin
 */
class Mentions extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'mentions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alert_mentionId', 'origin_id', 'created_time','message'], 'required'],
            [['alert_mentionId', 'origin_id', 'created_time', 'social_id', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
          //  [['mention_data'], 'save'],
            [['subject', 'message', 'message_markup', 'url', 'domain_url', 'location'], 'string', 'max' => 255],
            [['alert_mentionId'], 'exist', 'skipOnError' => true, 'targetClass' => AlertsMencions::className(), 'targetAttribute' => ['alert_mentionId' => 'id']],
            [['origin_id'], 'exist', 'skipOnError' => true, 'targetClass' => UsersMentions::className(), 'targetAttribute' => ['origin_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'alert_mentionId' => 'Alert Mention ID',
            'origin_id'       => 'Origin ID',
            'created_time'    => 'Created Time',
            'mention_data'    => 'Mention Data',
            'subject'         => 'Subject',
            'message'         => 'Message',
            'message_markup'  => 'Message Markup',
            'url'             => 'Url',
            'domain_url'      => 'Domain Url',
            'location'        => 'Location',
            'social_id'       => 'Social ID',
            'createdAt'       => 'Created At',
            'updatedAt'       => 'Updated At',
            'createdBy'       => 'Created By',
            'updatedBy'       => 'Updated By',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywordsMentions()
    {
        return $this->hasMany(KeywordsMentions::className(), ['mentionId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertMention()
    {
        return $this->hasOne(AlertsMencions::className(), ['id' => 'alert_mentionId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOrigin()
    {
        return $this->hasOne(UsersMentions::className(), ['id' => 'origin_id']);
    }
}
