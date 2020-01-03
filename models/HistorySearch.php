<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "history_search".
 *
 * @property int $id
 * @property int $alertId
 * @property string|null $search_data
 * @property int|null $createdAt
 * @property int|null $updatedAt
 * @property int|null $createdBy
 * @property int|null $updatedBy
 */
class HistorySearch extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'history_search';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['alertId'], 'required'],
            [['alertId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['search_data'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'alertId' => 'Alert ID',
            'search_data' => 'Search Data',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }
}
