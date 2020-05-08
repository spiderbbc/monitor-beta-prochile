<?php

namespace app\modules\topic\models;

use Yii;

/**
 * This is the model class for table "m_attachments".
 *
 * @property int $id
 * @property string|null $src_url
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 *
 * @property MStatistics $statistic
 */
class MAttachments extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'm_attachments';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
          //  [['statisticId'], 'required'],
            [[ 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['src_url'], 'string'],
           // [['statisticId'], 'exist', 'skipOnError' => true, 'targetClass' => MStatistics::className(), 'targetAttribute' => ['statisticId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
           //'statisticId' => Yii::t('app', 'Statistic ID'),
            'src_url' => Yii::t('app', 'Src Url'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * Gets query for [[MTopicsStadistics]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMTopicsStadistics()
    {
        return $this->hasMany(MTopicsStadistics::className(), ['attachmentId' => 'id']);
    }

    /**
     * Gets query for [[Statistic]].
     *
     * @return \yii\db\ActiveQuery
     */
    /*public function getStatistic()
    {
        return $this->hasOne(MStatistics::className(), ['id' => 'statisticId']);
    }*/
}
