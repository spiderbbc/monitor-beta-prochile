<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_topics_stadistics".
 *
 * @property int $id
 * @property int $topicId
 * @property int $resourceId
 * @property int $locationId
 * @property int $wordId
 * @property int|null $status
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MStatistics[] $mStatistics
 * @property MLocations $location
 * @property MResources $resource
 * @property MTopics $topic
 * @property MWords $word
 */
class MTopicsStadistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_topics_stadistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['topicId', 'resourceId', 'wordId'], 'required'],
            [['topicId', 'resourceId', 'locationId', 'wordId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['locationId'], 'exist', 'skipOnError' => true, 'targetClass' => MLocations::className(), 'targetAttribute' => ['locationId' => 'id']],
            [['resourceId'], 'exist', 'skipOnError' => true, 'targetClass' => MResources::className(), 'targetAttribute' => ['resourceId' => 'id']],
            [['topicId'], 'exist', 'skipOnError' => true, 'targetClass' => MTopics::className(), 'targetAttribute' => ['topicId' => 'id']],
            [['wordId'], 'exist', 'skipOnError' => true, 'targetClass' => MWords::className(), 'targetAttribute' => ['wordId' => 'id']],
            [['attachmentId'], 'exist', 'skipOnError' => true, 'targetClass' => MAttachments::className(), 'targetAttribute' => ['attachmentId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'topicId' => Yii::t('app', 'Topic ID'),
            'resourceId' => Yii::t('app', 'Resource ID'),
            'locationId' => Yii::t('app', 'Location ID'),
            'wordId' => Yii::t('app', 'Word ID'),
            'status' => Yii::t('app', 'Status'),
            'attachmentId' => Yii::t('app', 'Url'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MStatistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMStatistics()
    {
        return $this->hasMany(MStatistics::className(), ['topicStaticId' => 'id']);
    }

    /**
     * Gets query for [[Location]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLocation()
    {
        return $this->hasOne(MLocations::className(), ['id' => 'locationId']);
    }

    /**
     * Gets query for [[Resource]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResource()
    {
        return $this->hasOne(MResources::className(), ['id' => 'resourceId']);
    }

    /**
     * Gets query for [[Topic]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTopic()
    {
        return $this->hasOne(MTopics::className(), ['id' => 'topicId']);
    }

    /**
     * Gets query for [[Word]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getWord()
    {
        return $this->hasOne(MWords::className(), ['id' => 'wordId']);
    }

    /**
     * Gets query for [[Word]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function geMAttachments()
    {
        return $this->hasOne(MAttachments::className(), ['id' => 'attachmentId']);
    }
}
