<?php

use yii\db\Migration;

/**
 * Class m190813_203806_Alert_Config
 */
class m190813_203806_Alert_Config extends Migration
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

        $this->createTable('{{%Alert_Config}}', [
            'id'                    => $this->primaryKey(),
            'alertId'               => $this->integer(11)->notNull(),
            'product_description'   => $this->string(40)->notNull(),
            'competitors'           => $this->string(40)->notNull(),
            'countries'             => $this->string(40)->notNull(),
            'start_date'            => $this->integer(),
            'end_date'              => $this->integer(),
            'createdAt'             => $this->integer(),
            'updatedAt'             => $this->integer(),
            'createdBy'             => $this->integer(),
            'updatedBy'             => $this->integer(),

        ], $tableOptions);

        $this->insert('{{%Alert_Config}}', [
            'alertId'               => 1,
            'product_description'   => 'tecnology,Home entretaiment',
            'competitors'           => 'Sansung,Iphone,Hyundai',
            'countries'             => 'Chile',
            'start_date'            => '1488153462',
            'end_date'              => '1488153462',
            'createdAt'             => '1488153462',
            'updatedAt'             => '1488153462',
            'createdBy'             => '1',
            'updatedBy'             => '1',
        ]);

         // creates index for column `alertId`
        $this->createIndex(
            'idx-Alert_Config-alertId',
            'Alert_Config',
            'alertId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-Alert_Config-alertId',
            'Alert_Config',
            'alertId',
            'Alerts',
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
        $this->dropTable('{{%Alert_Config}}');
    }
}
