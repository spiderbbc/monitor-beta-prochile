<?php

use yii\db\Migration;

/**
 * Class m190813_213136_Products_Models
 */
class m190813_213136_Products_Models extends Migration
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

        $this->createTable('{{%products_models}}', [
            'id'                => $this->primaryKey(),
            'productId'         => $this->integer(11)->notNull(),
            'name'              => $this->string(),
            'status'            => $this->smallInteger(1)->defaultValue(1),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),
            'createdBy'         => $this->integer(),
            'updatedBy'         => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%products_models}}', [
            'productId'            => 1,
            'name'                  => 'Chile exports',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%products_models}}', [
            'productId'             => 2,
            'name'                  => 'Export Chile',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);


        // creates index for column `productId`
        $this->createIndex(
            'idx-products_models-category_productId',
            'products_models',
            'productId'
        );

        // relation
        // add foreign key for table `productId`
        $this->addForeignKey(
            'fk-products_models-category_productId',
            'products_models',
            'productId',
            'products',
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
       $this->dropTable('{{%products_models}}');
    }

}
