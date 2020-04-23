<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topics".
 *
 * @property int $id
 * @property int $userId
 * @property string|null $name
 * @property int|null $status
 * @property int|null $end_date
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MTopicResources[] $mTopicResources
 * @property Users $user
 * @property MTopicsDictionary[] $mTopicsDictionaries
 * @property MTopicsLocation[] $mTopicsLocations
 * @property MTopicsStadistics[] $mTopicsStadistics
 * @property MUrlsTopics[] $mUrlsTopics
 */
class MTopics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['userId'], 'required'],
            [['userId', 'status', 'end_date', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => \app\models\Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'userId' => Yii::t('app', 'User ID'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'end_date' => Yii::t('app', 'End Date'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MTopicResources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicResources()
    {
        return $this->hasMany(MTopicResources::className(), ['topicId' => 'id']);
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

    /**
     * Gets query for [[MTopicsDictionaries]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsDictionaries()
    {
        return $this->hasMany(MTopicsDictionary::className(), ['topicId' => 'id']);
    }

    /**
     * Gets query for [[MTopicsLocations]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsLocations()
    {
        return $this->hasMany(MTopicsLocation::className(), ['topicId' => 'id']);
    }

    /**
     * Gets query for [[MTopicsStadistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsStadistics()
    {
        return $this->hasMany(MTopicsStadistics::className(), ['topicId' => 'id']);
    }

    /**
     * Gets query for [[MUrlsTopics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMUrlsTopics()
    {
        return $this->hasMany(MUrlsTopics::className(), ['topicId' => 'id']);
    }
}
