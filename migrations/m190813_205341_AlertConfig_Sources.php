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

        $this->createTable('{{%alertConfig_sources}}',[
            'id'              => $this->primaryKey(),
            'alertconfigId'   => $this->integer()->notNull(),
            'alertResourceId' => $this->integer()->notNull(),
            'createdAt'       => $this->integer(),
            'updatedAt'       => $this->integer(),
            'createdBy'       => $this->integer(),
            'updatedBy'       => $this->integer(),

        ],$tableOptions);

        $this->insert('{{%alertConfig_sources}}', [
            'alertconfigId'   => 1,
            'alertResourceId' => 1,
            'createdAt'       => 1559312912,
            'updatedAt'       => 1559312912,
            'createdBy'       => 1,
            'updatedBy'       => 1,
        ]);


        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-alert_config_sources-alert_config',
            'alertConfig_sources',
            'alertconfigId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-alert_config_sources-alert_config',
            'alertConfig_sources',
            'alertconfigId',
            'alert_config',
            'id',
            'CASCADE',
            'CASCADE'
        );



        // creates index for column `dictionaryId`
        $this->createIndex(
            'idx-alert_config_sources-alert_sources',
            'alertConfig_sources',
            'alertResourceId'
        );

        // add foreign key for table `dictionaries`
        $this->addForeignKey(
            'fk-alert_config_sources-alert_sources',
            'alertConfig_sources',
            'alertResourceId',
            'resources',
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
        $this->dropTable('{{%alertConfig_sources}}');
    }

}
