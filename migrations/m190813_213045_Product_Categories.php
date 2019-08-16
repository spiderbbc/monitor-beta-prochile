<?php

use yii\db\Migration;

/**
 * Class m190813_213045_Product_Categories
 */
class m190813_213045_Product_Categories extends Migration
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

        $this->createTable('{{%Product_Categories}}', [
            'id'                => $this->primaryKey(),
            'products_familyId' => $this->integer(11)->notNull(),
            'name'              => $this->string(),
            'status'            => $this->smallInteger(1)->defaultValue(1),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),
            'createdBy'         => $this->integer(),
            'updatedBy'         => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%Product_Categories}}', [
            'products_familyId'     => 1,
            'name'                  => 'FullVision',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%Product_Categories}}', [
            'products_familyId'     => 2,
            'name'                  => 'HD',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);


        // creates index for column `products_familyId`
        $this->createIndex(
            'idx-products_categories-family_familyId',
            'Product_Categories',
            'products_familyId'
        );

        // relation
        // add foreign key for table `products_familyId`
        $this->addForeignKey(
            'fk-products_categories-family_familyId',
            'Product_Categories',
            'products_familyId',
            'Products_Family',
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
        $this->dropTable('{{%Product_Categories}}');
    }

}
