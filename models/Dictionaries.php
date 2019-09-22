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

    const FREE_WORDS_ID  = 4;
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

    public static function  saveDictionaryDrive($dictionaryIds, $alertId){
        if($dictionaryIds){
            $dictionaries = [];
            foreach ($dictionaryIds as $name) {
              $isDictionary = \app\models\Dictionaries::getDb()->cache(function ($db) use($name) {
                  return \app\models\Dictionaries::find()->where(['name' => $name])->exists();
              });    
              if(!$isDictionary){
                $color = substr(md5(time()), 0, 6);
                $dictionary = new \app\models\Dictionaries();
                $dictionary->name = $name;
                $dictionary->color = '#' . $color;
                $dictionary->save();
              }
              else{
                $dictionary = \app\models\Dictionaries::getDb()->cache(function ($db) use ($name) {
                    return \app\models\Dictionaries::find()->where(['name' => $name])->one();
                });
              }
               
              $dictionaries[$dictionary->id] = $dictionary->name;  
            } 
            if($dictionaries){
              $drive   = new \app\models\api\DriveApi();
              foreach ($dictionaries as $dictionaryId => $dictionaryName){
                $keywords_drive = $drive->getContentDictionaryByTitle([$dictionaryName]);
                foreach ($keywords_drive[$dictionaryName] as $word) {
                  $models[] = [$alertId,$dictionaryId,$word];
                }
                  
              }
            
            }
            if(!empty($models)){
              \app\models\Keywords::deleteAll('alertId = '.$alertId);
              Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
                        ->execute();
            }
        }
    }

    public static function saveFreeWords($free_words,$alertId){
      $model = [];
      foreach ($free_words as $word){
        $models[] = [$alertId,4,$word];
      }
      // save free words 
      Yii::$app->db->createCommand()->batchInsert('keywords', ['alertId','dictionaryId', 'name'],$models)
      ->execute();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['dictionaryId' => 'id']);
    }
}
