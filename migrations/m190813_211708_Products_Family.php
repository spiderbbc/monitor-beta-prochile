<?php

use yii\db\Migration;

/**
 * Class m190813_211708_Products_Family
 */
class m190813_211708_Products_Family extends Migration
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

        $this->createTable('{{%Products_Family}}', [
            'id'                => $this->primaryKey(),
            'seriesId'          => $this->integer(11)->notNull(),
            'name'              => $this->string(),
            'status'            => $this->smallInteger(1)->defaultValue(1),
            'createdAt'         => $this->integer(),
            'updatedAt'         => $this->integer(),
            'createdBy'         => $this->integer(),
            'updatedBy'         => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%Products_Family}}', [
            'seriesId'              => 1,
            'name'                  => 'Smartphones',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

        $this->insert('{{%Products_Family}}', [
            'seriesId'              => 2,
            'name'                  => 'Televisores',
            'status'                => 1,
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

         // creates index for column `seriesId`
        $this->createIndex(
            'idx-products_family-seriesId',
            'Products_Family',
            'seriesId'
        );

        // relation
        // add foreign key for table `seriesId`
        $this->addForeignKey(
            'fk-products_family-seriesId',
            'Products_Family',
            'seriesId',
            'Products_Series',
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
        $this->dropTable('{{%Products_Family}}');
    }
}
