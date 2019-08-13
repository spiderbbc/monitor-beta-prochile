<?php

use yii\db\Migration;

/**
 * Class m190813_205341_AlertConfig_Sources
 */
class m190813_205341_AlertConfig_Sources extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%AlertConfig_Sources}}',[
            'id'              => $this->primaryKey(),
            'alertconfigId'   => $this->integer()->notNull(),
            'alertResourceId' => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);


        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-alert_config_sources-alert_config',
            'AlertConfig_Sources',
            'alertconfigId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-alert_config_sources-alert_config',
            'AlertConfig_Sources',
            'alertconfigId',
            'Alert_Config',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-alert_config_sources-alert_sources',
            'AlertConfig_Sources',
            'alertResourceId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-alert_config_sources-alert_sources',
            'AlertConfig_Sources',
            'alertResourceId',
            'Resources',
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
        $this->dropTable('{{%AlertConfig_Sources}}');
    }

}
