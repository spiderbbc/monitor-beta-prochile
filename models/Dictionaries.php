<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "dictionaries".
 *
 * @property int $id
 * @property string $name
 * @property string $color
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Keywords[] $keywords
 */
class Dictionaries extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dictionaries';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'color'], 'required'],
            [['createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name', 'color'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'color' => 'Color',
            'createdAt' => 'Created At',
            'updatedAt' => 'Updated At',
            'createdBy' => 'Created By',
            'updatedBy' => 'Updated By',
        ];
    }

    public function getOrSaveDictionary($dictionaryIds){
        if($dictionaryIds){
            $dictionaries = [];
            foreach ($dictionaryIds as $name) {
              $isDictionary = \app\models\Dictionaries::find()->where( [ 'name' => $name ] )->exists(); 
              if(!$isDictionary){
                $color = substr(md5(time()), 0, 6);
                $dictionary = new \app\models\Dictionaries();
                $dictionary->name = $name;
                $dictionary->color = '#' . $color;
                if(!$dictionary->save()){
                  $error = true;
                }
              }
              else{
                $dictionary =\app\models\Dictionaries::find()->where(['name' => $name])->one();
              }
               
              $dictionaries[$dictionary->id] = $dictionary->name;  
            } 
            return $dictionaries;
        }
        return false;

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['dictionaryId' => 'id']);
    }
}
