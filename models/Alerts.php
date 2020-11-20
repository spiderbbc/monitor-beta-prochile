<?php

namespace app\models;

use Yii;
use Yii\helpers\ArrayHelper;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "alerts".
 *
 * @property int $id
 * @property int $userId
 * @property string $name
 * @property int $status
 * @property int $condicion
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property Users $user
 * @property Keywords[] $keywords
 */
class Alerts extends \yii\db\ActiveRecord
{
    public $files;
    public $free_words = [];
    public $dictionaryIds = [];
    public $productsIds = [];
    public $alertResourceId  = [];

    const STATUS_ACTIVE    = 1;
    const STATUS_INACTIVE  = 0;
    
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alerts';
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
            //[['userId','name','alertResourceId','productsIds'], 'required'],
            [['userId','name','alertResourceId','productsIds'], 'required', 'on' => 'saveOrUpdate'],
           // [['userId','name','alertResourceId','productsIds'], 'required'],
            [['status'], 'default','value' => 1],
            [['userId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'        => Yii::t('app', 'ID'),
            'userId'    => Yii::t('app', 'User ID'),
            'name'      => Yii::t('app', 'Nombre de la Alerta'),
            'status'    => Yii::t('app', 'Status'),
            'alertResourceId'=> Yii::t('app', 'Redes Sociales'),
            'dictionaryIds'  => Yii::t('app', 'Diccionarios de Palabras en Drive'),
            'free_words'  => Yii::t('app', 'Filtros Libres a Buscar'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }


    /**
     * [getBringAllAlertsToRun get all the alerts with resources,products only use to console actions]
     * @return [array] [if not alerts with condition return a empty array]
     */
    public function getBringAllAlertsToRun($read = false,$resourceName = ''){

        // get time
        $timestamp = \app\helpers\DateHelper::getToday();
        // get all alert with relation config with the condicion start_date less or equals to $timestamp
        $alerts = $this->find()->where([
            'status' => self::STATUS_ACTIVE,
        ])->with(['config' => function($query) use($timestamp,$read,$resourceName) {
            $query->andWhere([
                'and',
                    ['<=', 'start_date', $timestamp],
                ]);
            if ($read) {
                if($resourceName != ''){
                    $query->with(['configSources.alertResource' => function($query) use ($resourceName){
                         $query->andWhere(['name' => $resourceName]);
                    }]);
                    
                }else{
                    $query->with(['configSources.alertResource' => function($query) use ($resourceName){
                        $query->andWhere([
                        'and',
                            ['!=', 'name', 'Paginas Webs'],
                        ]);
                    }]);
                }
            }else{
                $query->with(['configSources.alertResource']);
            }
        }
        ])->orderBy('id DESC')->asArray()->all();

        $alertsConfig = null;
        
        // there is alert in the model
        if(!empty($alerts)){
            $alertsConfig = \app\helpers\AlertMentionsHelper::orderConfigSources($alerts);
            if ($read) {
                $alertsConfig = \app\helpers\AlertMentionsHelper::checksSourcesCall($alertsConfig);
            }
            $alertsConfig = \app\helpers\AlertMentionsHelper::setTermsSearch($alertsConfig);
        }
       
       return $alertsConfig;
    }
    /**
     * [getDictionaries get dictionaries for form]
     * @return [array] []
     */
    public function getDictionaries(){
        $dictionaries = Dictionaries::getDb()->cache(function ($db) {
            return Dictionaries::find()->all();
        },60);
        $dictionariesIds = \yii\helpers\ArrayHelper::map($dictionaries,'id','name');
        return $dictionariesIds;
    }

    
    public function getSocial(){
        $socials = Resources::getDb()->cache(function ($db) {
            return Resources::find()->where(['resourcesId' => [1,2,5]])->all();
        },60);
        $socialIds = \yii\helpers\ArrayHelper::map($socials,'id','name');
        return $socialIds;
    }

    /**
     * return free_words dictionaries words [form]
     * @return array
     */
    public function getFreeKeywords()
    {
        $words = [];
        $dictionary = \app\models\Dictionaries::find()->where(['name' => \app\models\Dictionaries::FREE_WORDS_NAME])->one();
        
        if ($dictionary) {
            $keywords =  $this->hasMany(Keywords::className(), ['alertId' => 'id'])
                ->where(['dictionaryId' => $dictionary->id])
                 ->select('name')
                 ->orderBy('id')
                 ->all();
            $words = [];     
            if($keywords){
              foreach ($keywords as $keyword){
                $words[] = $keyword->name;
              }
            }
        }
        return $words;     
    }
   


    /**
     * return  dictionaries name [form]
     * @return array
     */
    public function getDictionariesName(){
        $rows = (new \yii\db\Query())
        ->select('dictionaries.name')
        ->from('dictionaries')
        ->join('JOIN', 'keywords', 'keywords.dictionaryId = dictionaries.id')
        ->where(['keywords.alertId' => $this->id])
        ->groupBy('name')
        ->all();

        $dictionaryNames = [];

        for($r = 0; $r < sizeOf($rows); $r++){
            $dictionaryNames[$rows[$r]['name']] = $rows[$r]['name'];

        }

        return $dictionaryNames;
    }
    /**
     * return  products/models name [form]
     * @return array
     */
    public function getProducts(){

        /*$productsIds = ProductsModelsAlerts::find()->where(['alertId' => $this->id])->all();;*/

        /*
        $rows = (new \yii\db\Query())
        ->select(['products_models.id','products_models.name'])
        ->from('products_models')
        ->join('JOIN', 'products_models_alerts', 'products_models_alerts.product_modelId = products_models.id')
        ->where(['products_models_alerts.alertId' => $this->id])
        ->groupBy('products_models.name')
        ->all();
        
        $product_models = [];

        for($r = 0; $r < sizeOf($rows); $r++){
            $product_models[$rows[$r]['id']] = $rows[$r]['name'];

        }
        */

        $productsIds = ProductsModelsAlerts::find()->where(['alertId' => $this->id])->all();
        $product_models = [];

        foreach ($productsIds as $product) {
            $product_models[$product->productModel->id] = $product->productModel->name;
        }

        return $product_models;


    }

    public function getTermsFind(){
        $alertsMentions = AlertsMencions::find()->with('mentions')->where(['alertId' => $this->id])->all();
        $product_models = [];
        foreach($alertsMentions as $alertMention){
            if($alertMention->mentions){
                $product_models[$alertMention->term_searched] = $alertMention->term_searched;
            }
        }
        return $product_models;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getConfig()
    {
        return $this->hasOne(AlertConfig::className(), ['alertId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeywords()
    {
        return $this->hasMany(Keywords::className(), ['alertId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAlertsMentions()
    {
        return $this->hasMany(AlertsMencions::className(), ['alertId' => 'id']);
    }

}
