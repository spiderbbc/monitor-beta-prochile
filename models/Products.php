<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "products".
 *
 * @property int $id
 * @property int $categoryId
 * @property string $name
 * @property int $status
 * @property int $createdAt
 * @property int $updatedAt
 * @property int $createdBy
 * @property int $updatedBy
 *
 * @property ProductCategories $category
 * @property ProductsModels[] $productsModels
 */
class Products extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'products';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['categoryId'], 'required'],
            [['categoryId', 'status', 'createdAt', 'updatedAt', 'createdBy', 'updatedBy'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['categoryId'], 'exist', 'skipOnError' => true, 'targetClass' => ProductCategories::className(), 'targetAttribute' => ['categoryId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'categoryId' => Yii::t('app', 'Category ID'),
            'name' => Yii::t('app', 'Name'),
            'status' => Yii::t('app', 'Status'),
            'createdAt' => Yii::t('app', 'Created At'),
            'updatedAt' => Yii::t('app', 'Updated At'),
            'createdBy' => Yii::t('app', 'Created By'),
            'updatedBy' => Yii::t('app', 'Updated By'),
        ];
    }

    /**
     * [getProducts get all products by categories]
     * @return [array] []
     */
    public static function getProducts()
    {
        $seriesHeader = Yii::t('app', 'Series de productos');
        $familyHeader = Yii::t('app', 'Familia de productos');
        $categoriesHeader = Yii::t('app', 'Categoria de producto');
        $productsHeader = Yii::t('app', 'Productos');
        $modelsHeader = Yii::t('app', 'Modelos de producto');
        

        $series[$seriesHeader] = ArrayHelper::map(ProductsSeries::find()
                                                ->where(['status' => 1])
                                                ->all(),
                                            'id','name');
        
        $family[$familyHeader] = ArrayHelper::map(ProductsFamily::find()->andFilterCompare('seriesId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'id','name');
        
        $categories[$categoriesHeader] = ArrayHelper::map(ProductCategories::find()
                                                ->andFilterCompare('familyId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'id','name');
        
        $products[$productsHeader] = ArrayHelper::map(Products::find()
                                                ->andFilterCompare('categoryId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'id','name');
       

        $products_models[$modelsHeader] = ArrayHelper::map(ProductsModels::find()
                                                ->andFilterCompare('productId','null','<>')
                                                ->where(['status' => 1])
                                                ->all(),
                                            'id','name');

        $data = ArrayHelper::merge($series,$family);
        $data = ArrayHelper::merge($categories,$data);
        $data = ArrayHelper::merge($products,$data);
        $data = ArrayHelper::merge($products_models,$data);
        return $data;

    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ProductCategories::className(), ['id' => 'categoryId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProductsModels()
    {
        return $this->hasMany(ProductsModels::className(), ['productId' => 'id']);
    }
}
