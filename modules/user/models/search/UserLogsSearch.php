<?php

namespace app\modules\user\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\user\models\UserLogs;

/**
 * UserLogsSearch represents the model behind the search form of `app\modules\user\models\UserLogs`.
 */
class UserLogsSearch extends UserLogs
{
    public $username;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'userId', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['username','message'], 'string'],
           // [['username','message'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserLogs::find()->orderBy(['log_date' => SORT_DESC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $query->joinWith('user');

        $this->load($params);
        $dataProvider->sort->attributes['log_date'] = [
            'asc' => ['log_date' => SORT_ASC],
            'desc' => ['log_date' => SORT_DESC],
        ];

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'userId' => $this->userId,
            'log_date' => $this->log_date,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ]);
            
        $query->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', \app\models\Users::tableName() .'.id', $this->username]);
        

        return $dataProvider;
    }
}
