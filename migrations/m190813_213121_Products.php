<?php

use yii\db\Migration;

/**
 * Class m190813_213121_Products
 */
class m190813_213121_Products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%products}}', [
            'id'                => $this->primaryKey(),
            'categoryId' => $this->integer(11)->notNull(),
            'name'              => $this->string(),
            'status'            => $this->smallInteger(1)->defaultValue(1),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),
            'createdBy'         => $this->integer(),
            'updatedBy'         => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%products}}', [
            'categoryId'            => 1,
            'name'                  => 'Smartphone FullVision 6,1" QHD',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%products}}', [
            'categoryId'            => 2,
            'name'                  => 'SMART TV LED 32" HD 720p',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);


        // creates index for column `categoryId`
        $this->createIndex(
            'idx-products-category_categoryId',
            'products',
            'categoryId'
        );

        // relation
        // add foreign key for table `categoryId`
        $this->addForeignKey(
            'fk-products-category_categoryId',
            'products',
            'categoryId',
            'product_categories',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%products}}');
    }

}
